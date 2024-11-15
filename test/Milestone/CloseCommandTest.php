<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CloseCommand;
use Phly\KeepAChangelog\Milestone\CloseMilestoneEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->input->expects($this->any())->method('getArgument')->with('id')->willReturn('200');
    }

    public function testExecutionReturnsZeroOnSuccess(): void
    {
        $event = $this->createMock(CloseMilestoneEvent::class);
        $event->expects($this->once())->method('failed')->willReturn(false);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CloseMilestoneEvent $event): bool {
                TestCase::assertSame($this->input, $event->input());
                TestCase::assertSame($this->output, $event->output());
                TestCase::assertSame($this->dispatcher, $event->dispatcher());
                TestCase::assertSame(200, $event->id());

                return true;
            }))
            ->willReturn($event);

        $command = new CloseCommand($this->dispatcher);

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneOnFailure(): void
    {
        $event = $this->createMock(CloseMilestoneEvent::class);
        $event->expects($this->once())->method('failed')->willReturn(true);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CloseMilestoneEvent $event): bool {
                TestCase::assertSame($this->input, $event->input());
                TestCase::assertSame($this->output, $event->output());
                TestCase::assertSame($this->dispatcher, $event->dispatcher());
                TestCase::assertSame(200, $event->id());

                return true;
            }))
            ->willReturn($event);

        $command = new CloseCommand($this->dispatcher);

        $this->assertSame(1, $this->executeCommand($command));
    }
}
