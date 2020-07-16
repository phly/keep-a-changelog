<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Unreleased\PromoteCommand;
use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function date;

class PromoteCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    /** @var PromoteCommand */
    private $command;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    public function setUp() : void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->command    = new PromoteCommand($this->dispatcher->reveal());
    }

    public function providedInput() : iterable
    {
        yield 'failed-version-only'         => [$failed = true, $version = '2.5.0', $date = null];
        yield 'success-version-only'        => [$failed = false, $version = '2.5.0', $date = null];
        yield 'failed-version-and-date'     => [$failed = true, $version = '2.5.0', $date = '2020-07-16'];
        yield 'success-version-and-datenly' => [$failed = false, $version = '2.5.0', $date = '2020-07-16'];
    }

    /**
     * @dataProvider providedInput
     */
    public function testReturnsExpectedExitCodeBasedOnEventDispatchStatus(
        bool $failureStatus,
        string $version,
        ?string $date
    ) : void {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $date       = $date ?: date('Y-m-d');

        $input->getArgument('version')->willReturn($version);
        $input->getOption('date')->willReturn($date);

        /** @var PromoteEvent|ObjectProphecy $event */
        $event = $this->prophesize(PromoteEvent::class);
        $event->failed()->willReturn($failureStatus);

        $this->dispatcher
            ->dispatch(Argument::that(
                function ($event) use ($input, $output, $dispatcher, $version, $date) {
                    /** @var PromoteEvent $event */
                    TestCase::assertInstanceOf(PromoteEvent::class, $event);
                    TestCase::assertSame($input->reveal(), $event->input());
                    TestCase::assertSame($output->reveal(), $event->output());
                    TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                    TestCase::assertSame($version, $event->newVersion());
                    TestCase::assertSame($date, $event->releaseDate());

                    return $event;
                }
            ))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $expectedStatus = $failureStatus ? 1 : 0;
        $this->assertSame($expectedStatus, $this->executeCommand($this->command));
    }
}
