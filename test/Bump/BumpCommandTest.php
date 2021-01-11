<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpCommand;
use Phly\KeepAChangelog\Exception;
use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpCommandTest extends TestCase
{
    use ExecuteCommandTrait;
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function testConstructorRaisesExceptionForInvalidType()
    {
        $this->expectException(Exception\InvalidBumpTypeException::class);
        new BumpCommand('invalid-type', $this->dispatcher->reveal());
    }

    public function expectedTypes(): iterable
    {
        yield 'BUMP_MAJOR'      => [BumpCommand::BUMP_MAJOR, 'bumpMajorVersion'];
        yield 'BUMP_MINOR'      => [BumpCommand::BUMP_MINOR, 'bumpMinorVersion'];
        yield 'BUMP_PATCH'      => [BumpCommand::BUMP_PATCH, 'bumpPatchVersion'];
        yield 'BUMP_BUGFIX'     => [BumpCommand::BUMP_BUGFIX, 'bumpPatchVersion'];
        yield 'BUMP_UNRELEASED' => [BumpCommand::BUMP_UNRELEASED, BumpChangelogVersionEvent::UNRELEASED];
    }

    /**
     * @dataProvider expectedTypes
     */
    public function testConstructorAllowsExpectedTypes(string $bumpType)
    {
        $command = new BumpCommand($bumpType, $this->dispatcher->reveal());
        $this->assertInstanceOf(BumpCommand::class, $command);
    }

    /**
     * @dataProvider expectedTypes
     */
    public function testExecutionReturnsZeroOnSuccess(string $bumpType, string $methodName)
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(false);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher, $methodName) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());

                if ($methodName === BumpChangelogVersionEvent::UNRELEASED) {
                    TestCase::assertNull($event->bumpMethod());
                    TestCase::assertSame(BumpChangelogVersionEvent::UNRELEASED, $event->version());
                    return $event;
                }

                TestCase::assertSame($methodName, $event->bumpMethod());
                TestCase::assertNull($event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpCommand($bumpType, $this->dispatcher->reveal());

        $this->assertSame(0, $this->executeCommand($command));
    }

    /**
     * @dataProvider expectedTypes
     */
    public function testExecutionReturnsOneOnFailure(string $bumpType, string $methodName)
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(true);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher, $methodName) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());

                if ($methodName === BumpChangelogVersionEvent::UNRELEASED) {
                    TestCase::assertNull($event->bumpMethod());
                    TestCase::assertSame(BumpChangelogVersionEvent::UNRELEASED, $event->version());
                    return $event;
                }

                TestCase::assertSame($methodName, $event->bumpMethod());
                TestCase::assertNull($event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpCommand($bumpType, $this->dispatcher->reveal());

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
        $bumpEvent->version()->willReturn('2.0.1')->shouldBeCalled();
        $milestoneEvent->failed()->willReturn($failed)->shouldBeCalled();

        $dispatchCreateMilestoneEvent = function () use ($dispatcher, $bumpEvent, $milestoneEvent) {
            $dispatcher
                ->dispatch(Argument::that(function (CreateMilestoneEvent $event) {
                    TestCase::assertSame('2.0.1', $event->title());
                    return $event;
                }))
                ->will([$milestoneEvent, 'reveal']);
            return $bumpEvent->reveal();
        };

        $dispatcher
            ->dispatch(Argument::type(BumpChangelogVersionEvent::class))
            ->will($dispatchCreateMilestoneEvent);

        $this->input->getOption('create-milestone')->willReturn(true)->shouldBeCalled();
        $this->input->getOption('create-milestone-with-name')->willReturn(null)->shouldBeCalled();

        $command = new BumpCommand(BumpCommand::BUMP_PATCH, $this->dispatcher->reveal());

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
        $bumpEvent->version()->willReturn('2.0.0')->shouldBeCalled();
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

        $this->input->getOption('create-milestone')->willReturn(null)->shouldBeCalled();
        $this->input->getOption('create-milestone-with-name')->willReturn('2.0.0 The Big Kahuna')->shouldBeCalled();

        $command = new BumpCommand(BumpCommand::BUMP_PATCH, $this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
