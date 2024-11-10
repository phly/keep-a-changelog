<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReadyCommand;
use Phly\KeepAChangelog\Version\ReadyLatestChangelogEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_merge;
use function date;
use function sprintf;

class ReadyCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function inputOptions(): iterable
    {
        $cases = [
            'defaults' => [null, null, date('Y-m-d'), null],
            'custom'   => ['2018-06-01', '1.0.0', '2018-06-01', '1.0.0'],
        ];

        foreach ([true, false] as $failed) {
            foreach ($cases as $type => $defaults) {
                $name      = sprintf('%s - %s', $failed ? 'failed' : 'succeeded', $type);
                $arguments = array_merge([$failed], $defaults);
                yield $name => $arguments;
            }
        }
    }

    /**
     * @dataProvider inputOptions
     */
    public function testReturnsExpectedExitCodeBasedOnEventDispatchStatus(
        bool $failureStatus,
        ?string $releaseDate,
        ?string $releaseVersion,
        string $expectedDate,
        ?string $expectedVersion
    ) {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;

        $input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['date', $releaseDate],
                ['release-version', $releaseVersion],
            ]));

        $event = $this->createMock(ReadyLatestChangelogEvent::class);
        $event->expects($this->atLeastOnce())->method('failed')->willReturn($failureStatus);

        $dispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->with($this->callback(
                function ($event) use ($input, $output, $dispatcher, $expectedDate, $expectedVersion) {
                    TestCase::assertInstanceOf(ReadyLatestChangelogEvent::class, $event);
                    TestCase::assertSame($input, $event->input());
                    TestCase::assertSame($output, $event->output());
                    TestCase::assertSame($dispatcher, $event->dispatcher());
                    TestCase::assertSame($expectedDate, $event->releaseDate());
                    TestCase::assertSame($expectedVersion, $event->version());

                    return true;
                }
            ))
            ->willReturn($event);

        $command = new ReadyCommand($dispatcher);

        $expectedStatus = $failureStatus ? 1 : 0;
        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
