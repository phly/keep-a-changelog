<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Version\ShowVersionEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowVersionEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function createEvent(?string $version = null): ShowVersionEvent
    {
        return new ShowVersionEvent($this->input, $this->output, $this->dispatcher, $version);
    }

    public function testEventImplementsPackageEvent(): ShowVersionEvent
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(EventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testEventImplementsChangelogAwareEvent(ShowVersionEvent $event)
    {
        $this->assertInstanceOf(ChangelogEntryAwareEventInterface::class, $event);
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testPropagationIsNotStoppedByDefault(ShowVersionEvent $event)
    {
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testIsNotFailedByDefault(ShowVersionEvent $event)
    {
        $this->assertFalse($event->failed());
    }
}
