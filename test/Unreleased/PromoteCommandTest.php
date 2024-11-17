<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use Phly\KeepAChangelog\Unreleased\PromoteCommand;
use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function date;

class PromoteCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    private PromoteCommand $command;
    private EventDispatcherInterface&MockObject $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->command    = new PromoteCommand($this->dispatcher);
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
        $date = $date ?: date('Y-m-d');

        $this->input->expects($this->atLeastOnce())->method('getArgument')->with('version')->willReturn($version);
        $this->input
            ->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap([
                ['date', $date],
                ['create-milestone', false],
                ['create-milestone->with-name', null],
            ]));

        $event = $this->createMock(PromoteEvent::class);
        $event->expects($this->once())->method('failed')->willReturn($failureStatus);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (PromoteEvent $event) use ($version, $date): bool {
                    TestCase::assertSame($this->input, $event->input());
                    TestCase::assertSame($this->output, $event->output());
                    TestCase::assertSame($this->dispatcher, $event->dispatcher());
                    TestCase::assertSame($version, $event->newVersion());
                    TestCase::assertSame($date, $event->releaseDate());

                    return true;
            }))
            ->willReturn($event);

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
        $version        = '1.2.3';
        $date           = date('Y-m-d');
        $promoteEvent   = $this->createMock(PromoteEvent::class);
        $milestoneEvent = $this->createMock(CreateMilestoneEvent::class);

        $promoteEvent->expects($this->atLeastOnce())->method('failed')->willReturn(false);
        $milestoneEvent->expects($this->atLeastOnce())->method('failed')->willReturn($failed);

        $this->input->expects($this->atLeastOnce())->method('getArgument')->with('version')->willReturn($version);
        $this->input
            ->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap([
                ['date', $date],
                ['create-milestone', true],
                ['create-milestone-with-name', null],
            ]));

        $invokedCount = $this->exactly(2);
        $this->dispatcher
            ->expects($invokedCount)
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($invokedCount, $version): bool {
                if ($invokedCount->getInvocationCount() === 1) {
                    TestCase::assertInstanceOf(PromoteEvent::class, $event);
                    return true;
                }
                if ($invokedCount->getInvocationCount() === 2) {
                    TestCase::assertInstanceOf(CreateMilestoneEvent::class, $event);
                    TestCase::assertSame($version, $event->title());
                    return true;
                }
            }))
            ->will($this->returnCallback(function () use ($invokedCount, $promoteEvent, $milestoneEvent) {
                if ($invokedCount->getInvocationCount() === 1) {
                    return $promoteEvent;
                }
                if ($invokedCount->getInvocationCount() === 2) {
                    return $milestoneEvent;
                }
            }));

        $command = new PromoteCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }

    /**
     * @dataProvider expectedMilestoneCreationStatuses
     */
    public function testDispatchesCreateMilestoneEventWithNameWhenRequested(
        bool $failed,
        int $expectedStatus
    ): void {
        $date           = date('Y-m-d');
        $promoteEvent   = $this->createMock(PromoteEvent::class);
        $milestoneEvent = $this->createMock(CreateMilestoneEvent::class);

        $promoteEvent->expects($this->atLeastOnce())->method('failed')->willReturn(false);
        $milestoneEvent->expects($this->atLeastOnce())->method('failed')->willReturn($failed);

        $this->input->expects($this->atLeastOnce())->method('getArgument')->with('version')->willReturn('2.0.0');
        $this->input
            ->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap([
                ['date', $date],
                ['create-milestone', null],
                ['create-milestone-with-name', '2.0.0 The Big Kahuna'],
            ]));

        $invokedCount = $this->exactly(2);
        $this->dispatcher
            ->expects($invokedCount)
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($invokedCount): bool {
                if ($invokedCount->getInvocationCount() === 1) {
                    TestCase::assertInstanceOf(PromoteEvent::class, $event);
                    return true;
                }
                if ($invokedCount->getInvocationCount() === 2) {
                    TestCase::assertInstanceOf(CreateMilestoneEvent::class, $event);
                    TestCase::assertSame('2.0.0 The Big Kahuna', $event->title());
                    return true;
                }
            }))
            ->will($this->returnCallback(function () use ($invokedCount, $promoteEvent, $milestoneEvent) {
                if ($invokedCount->getInvocationCount() === 1) {
                    return $promoteEvent;
                }
                if ($invokedCount->getInvocationCount() === 2) {
                    return $milestoneEvent;
                }
            }));

        $command = new PromoteCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
