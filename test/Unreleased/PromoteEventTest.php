<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PromoteEventTest extends TestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    public function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testConstructorSetsVersionNewVersionAndReleaseDate(): void
    {
        $event = new PromoteEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '2.5.0',
            '2020-07-16'
        );

        $this->assertSame('unreleased', $event->version());
        $this->assertSame('2.5.0', $event->newVersion());
        $this->assertSame('2020-07-16', $event->releaseDate());
    }

    public function testCallingDidNotPromoteStopsPropagationAndEmitsOutput(): void
    {
        $event = new PromoteEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '2.5.0',
            '2020-07-16'
        );

        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('Invalid date'));

        $event->didNotPromote();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testCallingChangelogReadyEmitsOutputButDoesNotStopPropagation(): void
    {
        $event = new PromoteEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '2.5.0',
            '2020-07-16'
        );

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Renamed Unreleased entry to "2.5.0" with release date of "2020-07-16"'));

        $event->changelogReady();
        $this->assertFalse($event->isPropagationStopped());
    }
}
