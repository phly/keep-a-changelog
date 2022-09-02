<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\ListCommand;
use Phly\KeepAChangelog\Milestone\ListMilestonesEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommandTest extends TestCase
{
    use ExecuteCommandTrait;
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function testExecutionReturnsZeroOnSuccess(): void
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(ListMilestonesEvent::class);
        $event->failed()->willReturn(false);

        $dispatcher
            ->dispatch(Argument::that(function (ListMilestonesEvent $event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());

                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new ListCommand($this->dispatcher->reveal());

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneOnFailure(): void
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(ListMilestonesEvent::class);
        $event->failed()->willReturn(true);

        $dispatcher
            ->dispatch(Argument::that(function (ListMilestonesEvent $event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());

                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new ListCommand($this->dispatcher->reveal());

        $this->assertSame(1, $this->executeCommand($command));
    }
}
