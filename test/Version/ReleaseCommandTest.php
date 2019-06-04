<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReleaseCommand;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseCommandTest extends TestCase
{
    public function setUp()
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->event      = $this->prophesize(ReleaseEvent::class);
    }

    public function executeCommand(ReleaseCommand $command) : int
    {
        $r = new ReflectionMethod($command, 'execute');
        $r->setAccessible(true);
        return $r->invoke($command, $this->input->reveal(), $this->output->reveal());
    }

    public function createCommand() : ReleaseCommand
    {
        return new ReleaseCommand($this->dispatcher->reveal());
    }

    public function testExecutionReturnsFailedStatusWhenEventReturnsFailedStatus()
    {
        $event = $this->event;
        $event->failed()->willReturn(true);

        $this->dispatcher
            ->dispatch(Argument::type(ReleaseEvent::class))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = $this->createCommand();

        $this->assertSame(1, $this->executeCommand($command));
    }

    public function testExecutionReturnsSuccessStatusWhenEventDoesNotFail()
    {
        $event = $this->event;
        $event->failed()->willReturn(false);

        $this->dispatcher
            ->dispatch(Argument::type(ReleaseEvent::class))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = $this->createCommand();

        $this->assertSame(0, $this->executeCommand($command));
    }
}
