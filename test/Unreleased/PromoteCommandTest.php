<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use Phly\KeepAChangelog\Unreleased\PromoteCommand;
use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function date;

class PromoteCommandTest extends TestCase
{
    use ExecuteCommandTrait;
    use ProphecyTrait;

    /** @var PromoteCommand */
    private $command;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->command    = new PromoteCommand($this->dispatcher->reveal());
    }

    public function providedInput(): iterable
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
    ): void {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $date       = $date ?: date('Y-m-d');

        $input->getArgument('version')->willReturn($version);
        $input->getOption('date')->willReturn($date);
        $input->getOption('create-milestone')->willReturn(false);
        $input->getOption('create-milestone-with-name')->willReturn(null);

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

    public function expectedMilestoneCreationStatuses(): iterable
    {
        yield 'success' => [$failed = false, $status = 0];
        yield 'failed'  => [$failed = true, $status = 1];
    }

    /**
     * @dataProvider expectedMilestoneCreationStatuses
     */
    public function testDispatchesCreateMilestoneEventWithPromotedVersionWhenRequested(
        bool $failed,
        int $expectedStatus
    ): void {
        $version    = '1.2.3';
        $date       = date('Y-m-d');
        $dispatcher = $this->dispatcher;
        /** @var PromoteEvent|ObjectProphecy $promoteEvent */
        $promoteEvent = $this->prophesize(PromoteEvent::class);
        /** @var CreateMilestoneEvent|ObjectProphecy $milestoneEvent */
        $milestoneEvent = $this->prophesize(CreateMilestoneEvent::class);

        $promoteEvent->failed()->willReturn(false)->shouldBeCalled();
        $milestoneEvent->failed()->willReturn($failed)->shouldBeCalled();

        $dispatchCreateMilestoneEvent = function () use ($version, $dispatcher, $promoteEvent, $milestoneEvent) {
            $dispatcher
                ->dispatch(Argument::that(function (CreateMilestoneEvent $event) use ($version) {
                    TestCase::assertSame($version, $event->title());
                    return $event;
                }))
                ->will([$milestoneEvent, 'reveal']);
            return $promoteEvent->reveal();
        };

        $dispatcher
            ->dispatch(Argument::type(PromoteEvent::class))
            ->will($dispatchCreateMilestoneEvent);

        $this->input->getArgument('version')->willReturn($version);
        $this->input->getOption('date')->willReturn($date);
        $this->input->getOption('create-milestone')->willReturn(true)->shouldBeCalled();
        $this->input->getOption('create-milestone-with-name')->willReturn(null)->shouldBeCalled();

        $command = new PromoteCommand($this->dispatcher->reveal());

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
        $date       = date('Y-m-d');
        /** @var PromoteEvent|ObjectProphecy $promoteEvent */
        $promoteEvent = $this->prophesize(PromoteEvent::class);
        /** @var CreateMilestoneEvent|ObjectProphecy $milestoneEvent */
        $milestoneEvent = $this->prophesize(CreateMilestoneEvent::class);

        $promoteEvent->failed()->willReturn(false)->shouldBeCalled();
        $milestoneEvent->failed()->willReturn($failed)->shouldBeCalled();

        $dispatchCreateMilestoneEvent = function () use ($dispatcher, $promoteEvent, $milestoneEvent) {
            $dispatcher
                ->dispatch(Argument::that(function (CreateMilestoneEvent $event) {
                    TestCase::assertSame('2.0.0 The Big Kahuna', $event->title());
                    return $event;
                }))
                ->will([$milestoneEvent, 'reveal']);
            return $promoteEvent->reveal();
        };

        $dispatcher
            ->dispatch(Argument::type(PromoteEvent::class))
            ->will($dispatchCreateMilestoneEvent);

        $this->input->getArgument('version')->willReturn('2.0.0');
        $this->input->getOption('date')->willReturn($date);
        $this->input->getOption('create-milestone')->willReturn(null)->shouldBeCalled();
        $this->input->getOption('create-milestone-with-name')->willReturn('2.0.0 The Big Kahuna')->shouldBeCalled();

        $command = new PromoteCommand($this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
