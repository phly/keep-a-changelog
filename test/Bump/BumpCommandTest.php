<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpCommand;
use Phly\KeepAChangelog\Exception;
use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testConstructorRaisesExceptionForInvalidType()
    {
        $this->expectException(Exception\InvalidBumpTypeException::class);
        new BumpCommand('invalid-type', $this->dispatcher);
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
        $command = new BumpCommand($bumpType, $this->dispatcher);
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
        $event      = $this->createMock(BumpChangelogVersionEvent::class);
        $event->expects($this->any())->method('failed')->willReturn(false);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($input, $output, $dispatcher, $methodName) {
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertSame($dispatcher, $event->dispatcher());

                if ($methodName === BumpChangelogVersionEvent::UNRELEASED) {
                    TestCase::assertNull($event->bumpMethod());
                    TestCase::assertSame(BumpChangelogVersionEvent::UNRELEASED, $event->version());
                    return true;
                }

                TestCase::assertSame($methodName, $event->bumpMethod());
                TestCase::assertNull($event->version());
                return true;
            }))
            ->will($this->returnCallback(function () use ($event) {
                return $event;
            }));

        $command = new BumpCommand($bumpType, $this->dispatcher);

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
        $event      = $this->createMock(BumpChangelogVersionEvent::class);
        $event->expects($this->any())->method('failed')->willReturn(true);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($input, $output, $dispatcher, $methodName) {
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertSame($dispatcher, $event->dispatcher());

                if ($methodName === BumpChangelogVersionEvent::UNRELEASED) {
                    TestCase::assertNull($event->bumpMethod());
                    TestCase::assertSame(BumpChangelogVersionEvent::UNRELEASED, $event->version());
                    return true;
                }

                TestCase::assertSame($methodName, $event->bumpMethod());
                TestCase::assertNull($event->version());
                return true;
            }))
            ->will($this->returnCallback(function () use ($event) {
                return $event;
            }));

        $command = new BumpCommand($bumpType, $this->dispatcher);

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
        $bumpEvent->expects($this->atLeastOnce())->method('version')->willReturn('2.0.1');
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
                $this->assertSame('2.0.1', $event->title());
                return $milestoneEvent;
            }));

        $this->input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['create-milestone', true],
                ['create-milestone-with-name', null],
            ]));

        $command = new BumpCommand(BumpCommand::BUMP_PATCH, $this->dispatcher);

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
        /** @var BumpChangelogVersionEvent|MockObject $bumpEvent */
        $bumpEvent = $this->createMock(BumpChangelogVersionEvent::class);
        /** @var CreateMilestoneEvent|MockObject $milestoneEvent */
        $milestoneEvent = $this->createMock(CreateMilestoneEvent::class);

        $bumpEvent->expects($this->atLeastOnce())->method('failed')->willReturn(false);
        $bumpEvent->expects($this->atLeastOnce())->method('version')->willReturn('2.0.0');
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

        $this->input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['create-milestone', null],
                ['create-milestone-with-name', '2.0.0 The Big Kahuna'],
            ]));
        
        $command = new BumpCommand(BumpCommand::BUMP_PATCH, $this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
