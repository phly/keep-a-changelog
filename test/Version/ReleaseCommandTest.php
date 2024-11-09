<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReleaseCommand;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseCommandTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;
    private ReleaseEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->event      = $this->createMock(ReleaseEvent::class);
    }

    public function executeCommand(ReleaseCommand $command): int
    {
        $r = new ReflectionMethod($command, 'execute');
        $r->setAccessible(true);
        return $r->invoke($command, $this->input, $this->output);
    }

    public function createCommand(): ReleaseCommand
    {
        return new ReleaseCommand($this->dispatcher);
    }

    public function testExecutionReturnsFailedStatusWhenEventReturnsFailedStatus()
    {
        $event = $this->event;
        $event->expects($this->atLeastOnce())->method('failed')->willReturn(true);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ReleaseEvent::class))
            ->willReturn($event);

        $command = $this->createCommand();

        $this->assertSame(1, $this->executeCommand($command));
    }

    public function testExecutionReturnsSuccessStatusWhenEventDoesNotFail()
    {
        $event = $this->event;
        $event->expects($this->atLeastOnce())->method('failed')->willReturn(false);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ReleaseEvent::class))
            ->willReturn($event);

        $command = $this->createCommand();

        $this->assertSame(0, $this->executeCommand($command));
    }
}
