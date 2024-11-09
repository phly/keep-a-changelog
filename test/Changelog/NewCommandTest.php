<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\CreateNewChangelogEvent;
use Phly\KeepAChangelog\Changelog\NewCommand;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_merge;
use function sprintf;

class NewCommandTest extends TestCase
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
            'defaults' => [null, null, '0.1.0', false],
            'custom'   => ['1.0.0', true, '1.0.0', true],
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
        ?string $initialVersion,
        ?bool $overwrite,
        string $expectedVersion,
        bool $expectedOverwrite
    ) {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;

        $input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['initial-version', $initialVersion],
                ['overwrite', $overwrite],
            ]));

        $event = $this->createMock(CreateNewChangelogEvent::class);
        $event->expects($this->atLeastOnce())->method('failed')->willReturn($failureStatus);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                function ($event) use ($input, $output, $dispatcher, $expectedVersion, $expectedOverwrite) {
                    TestCase::assertInstanceOf(CreateNewChangelogEvent::class, $event);
                    TestCase::assertSame($input, $event->input());
                    TestCase::assertSame($output, $event->output());
                    TestCase::assertSame($dispatcher, $event->dispatcher());
                    TestCase::assertSame($expectedVersion, $event->version());
                    TestCase::assertSame($expectedOverwrite, $event->overwrite());

                    return true;
                }
            ))
            ->willReturn($event);

        $command = new NewCommand($dispatcher);

        $expectedStatus = $failureStatus ? 1 : 0;
        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
