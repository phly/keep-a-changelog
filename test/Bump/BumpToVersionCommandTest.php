<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpToVersionCommand;
use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpToVersionCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function testExecutionReturnsZeroOnSuccess(): void
    {
        $input = $this->input;
        $input->getArgument('version')->willReturn('1.2.3');
        $input->getOption('create-milestone')->willReturn(false);
        $input->getOption('create-milestone-with-name')->willReturn(null);
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(false);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertNull($event->bumpMethod());
                TestCase::assertSame('1.2.3', $event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpToVersionCommand($this->dispatcher->reveal());

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneOnFailure(): void
    {
        $input = $this->input;
        $input->getArgument('version')->willReturn('1.2.3');
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(true);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertNull($event->bumpMethod());
                TestCase::assertSame('1.2.3', $event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpToVersionCommand($this->dispatcher->reveal());

        $this->assertSame(1, $this->executeCommand($command));
    }

    public function expectedMilestoneCreationStatuses(): iterable
    {
        yield 'success' => [$failed = false, $status = 0];
        yield 'failed'  => [$failed = true, $status = 1];
    }

    /**
     * @dataProvider expectedMilestoneCreationStatuses
     */
    public function testDispatchesCreateMilestoneEventWithBumpedVersionWhenRequested(
        bool $failed,
        int $expectedStatus
    ): void {
        $dispatcher = $this->dispatcher;
        /** @var BumpChangelogVersionEvent|ObjectProphecy $bumpEvent */
        $bumpEvent = $this->prophesize(BumpChangelogVersionEvent::class);
        /** @var CreateMilestoneEvent|ObjectProphecy $milestoneEvent */
        $milestoneEvent = $this->prophesize(CreateMilestoneEvent::class);

        $bumpEvent->failed()->willReturn(false)->shouldBeCalled();
        $milestoneEvent->failed()->willReturn($failed)->shouldBeCalled();

        $dispatchCreateMilestoneEvent = function () use ($dispatcher, $bumpEvent, $milestoneEvent) {
            $dispatcher
                ->dispatch(Argument::that(function (CreateMilestoneEvent $event) {
                    TestCase::assertSame('1.2.3', $event->title());
                    return $event;
                }))
                ->will([$milestoneEvent, 'reveal']);
            return $bumpEvent->reveal();
        };

        $dispatcher
            ->dispatch(Argument::type(BumpChangelogVersionEvent::class))
            ->will($dispatchCreateMilestoneEvent);

        $this->input->getArgument('version')->willReturn('1.2.3');
        $this->input->getOption('create-milestone')->willReturn(true)->shouldBeCalled();
        $this->input->getOption('create-milestone-with-name')->willReturn(null)->shouldBeCalled();

        $command = new BumpToVersionCommand($this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }

    /**
     * @dataProvider expectedMilestoneCreationStatuses
     */
    public function testDispatchesCreateMilestoneEventWithNameWhenRequested(
        bool $failed,
        int $expectedStatus
    ): void {
        $dispatcher = $this->dispatcher;
        /** @var BumpChangelogVersionEvent|ObjectProphecy $bumpEvent */
        $bumpEvent = $this->prophesize(BumpChangelogVersionEvent::class);
        /** @var CreateMilestoneEvent|ObjectProphecy $milestoneEvent */
        $milestoneEvent = $this->prophesize(CreateMilestoneEvent::class);

        $bumpEvent->failed()->willReturn(false)->shouldBeCalled();
        $milestoneEvent->failed()->willReturn($failed)->shouldBeCalled();

        $dispatchCreateMilestoneEvent = function () use ($dispatcher, $bumpEvent, $milestoneEvent) {
            $dispatcher
                ->dispatch(Argument::that(function (CreateMilestoneEvent $event) {
                    TestCase::assertSame('2.0.0 The Big Kahuna', $event->title());
                    return $event;
                }))
                ->will([$milestoneEvent, 'reveal']);
            return $bumpEvent->reveal();
        };

        $dispatcher
            ->dispatch(Argument::type(BumpChangelogVersionEvent::class))
            ->will($dispatchCreateMilestoneEvent);

        $this->input->getArgument('version')->willReturn('2.0.0');
        $this->input->getOption('create-milestone')->willReturn(null)->shouldBeCalled();
        $this->input->getOption('create-milestone-with-name')->willReturn('2.0.0 The Big Kahuna')->shouldBeCalled();

        $command = new BumpToVersionCommand($this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
