<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReadyCommand;
use Phly\KeepAChangelog\Version\ReadyLatestChangelogEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_merge;
use function date;
use function sprintf;

class ReadyCommandTest extends TestCase
{
    use ExecuteCommandTrait;
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
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

        $input->getOption('date')->willReturn($releaseDate);
        $input->getOption('release-version')->willReturn($releaseVersion);

        $event = $this->prophesize(ReadyLatestChangelogEvent::class);
        $event->failed()->willReturn($failureStatus);

        $dispatcher
            ->dispatch(Argument::that(
                function ($event) use ($input, $output, $dispatcher, $expectedDate, $expectedVersion) {
                    TestCase::assertInstanceOf(ReadyLatestChangelogEvent::class, $event);
                    TestCase::assertSame($input->reveal(), $event->input());
                    TestCase::assertSame($output->reveal(), $event->output());
                    TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                    TestCase::assertSame($expectedDate, $event->releaseDate());
                    TestCase::assertSame($expectedVersion, $event->version());

                    return $event;
                }
            ))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new ReadyCommand($dispatcher->reveal());

        $expectedStatus = $failureStatus ? 1 : 0;
        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
