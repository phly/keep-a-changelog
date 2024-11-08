<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpToVersionCommand;
use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpToVersionCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testExecutionReturnsZeroOnSuccess(): void
    {
        $input = $this->input;

        $input->expects($this->atLeastOnce())->method('getArgument')->with('version')->willReturn('1.2.3');
        $input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['create-milestone', false],
                ['create-milestone-with-name', null],
            ]));
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->createMock(BumpChangelogVersionEvent::class);
        $event->expects($this->atLeastOnce())->method('failed')->willReturn(false);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertSame($dispatcher, $event->dispatcher());
                TestCase::assertNull($event->bumpMethod());
                TestCase::assertSame('1.2.3', $event->version());
                return true;
            }))
            ->willReturn($event);

        $command = new BumpToVersionCommand($this->dispatcher);

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneOnFailure(): void
    {
        $input = $this->input;
        $input->expects($this->atLeastOnce())->method('getArgument')->willReturn('1.2.3');
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->createMock(BumpChangelogVersionEvent::class);
        $event->expects($this->atLeastOnce())->method('failed')->willReturn(true);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertSame($dispatcher, $event->dispatcher());
                TestCase::assertNull($event->bumpMethod());
                TestCase::assertSame('1.2.3', $event->version());
                return true;
            }))
            ->willReturn($event);

        $command = new BumpToVersionCommand($this->dispatcher);

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
        /** @var BumpChangelogVersionEvent&MockObject $bumpEvent */
        $bumpEvent = $this->createMock(BumpChangelogVersionEvent::class);
        /** @var CreateMilestoneEvent&MockObject $milestoneEvent */
        $milestoneEvent = $this->createMock(CreateMilestoneEvent::class);

        $bumpEvent->expects($this->atLeastOnce())->method('failed')->willReturn(false);
        $milestoneEvent->expects($this->atLeastOnce())->method('failed')->willReturn($failed);

        $invokedCount = $this->exactly(2);

        $dispatcher
            ->expects($invokedCount)
            ->method('dispatch')
            ->will($this->returnCallback(function ($event) use ($invokedCount, $bumpEvent, $milestoneEvent) {
                if ($invokedCount->getInvocationCount() === 1) {
                    $this->assertInstanceOf(BumpChangelogVersionEvent::class, $event);
                    return $bumpEvent;
                }

                $this->assertInstanceOf(CreateMilestoneEvent::class, $event);
                $this->assertSame('1.2.3', $event->title());
                return $milestoneEvent;
            }));

        $input = $this->input;
        $input->expects($this->atLeastOnce())->method('getArgument')->with('version')->willReturn('1.2.3');
        $input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['create-milestone', true],
                ['create-milestone-with-name', null],
            ]));

        $command = new BumpToVersionCommand($this->dispatcher);

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
        /** @var BumpChangelogVersionEvent&MockObject $bumpEvent */
        $bumpEvent = $this->createMock(BumpChangelogVersionEvent::class);
        /** @var CreateMilestoneEvent&MockObject $milestoneEvent */
        $milestoneEvent = $this->createMock(CreateMilestoneEvent::class);

        $bumpEvent->expects($this->atLeastOnce())->method('failed')->willReturn(false);
        $milestoneEvent->expects($this->atLeastOnce())->method('failed')->willReturn($failed);

        $invokedCount = $this->exactly(2);

        $dispatcher
            ->expects($invokedCount)
            ->method('dispatch')
            ->will($this->returnCallback(function ($event) use ($invokedCount, $bumpEvent, $milestoneEvent) {
                if ($invokedCount->getInvocationCount() === 1) {
                    $this->assertInstanceOf(BumpChangelogVersionEvent::class, $event);
                    return $bumpEvent;
                }

                $this->assertInstanceOf(CreateMilestoneEvent::class, $event);
                $this->assertSame('2.0.0 The Big Kahuna', $event->title());
                return $milestoneEvent;
            }));


        $input = $this->input;
        $input->expects($this->atLeastOnce())->method('getArgument')->with('version')->willReturn('2.0.0');
        $input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['create-milestone', null],
                ['create-milestone-with-name', '2.0.0 The Big Kahuna'],
            ]));

        $command = new BumpToVersionCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
