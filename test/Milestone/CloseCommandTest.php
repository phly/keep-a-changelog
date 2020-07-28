<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CloseCommand;
use Phly\KeepAChangelog\Milestone\CloseMilestoneEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    protected function setUp() : void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->input->getArgument('id')->willReturn('200');
    }

    public function testExecutionReturnsZeroOnSuccess() : void
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(CloseMilestoneEvent::class);
        $event->failed()->willReturn(false);

        $dispatcher
            ->dispatch(Argument::that(function (CloseMilestoneEvent $event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertSame(200, $event->id());

                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new CloseCommand($this->dispatcher->reveal());

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneOnFailure() : void
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(CloseMilestoneEvent::class);
        $event->failed()->willReturn(true);

        $dispatcher
            ->dispatch(Argument::that(function (CloseMilestoneEvent $event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertSame(200, $event->id());

                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new CloseCommand($this->dispatcher->reveal());

        $this->assertSame(1, $this->executeCommand($command));
    }
}
