<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveChangelogVersionEventTest extends TestCase
{
    private Config&MockObject $config;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->config     = $this->createMock(Config::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->config->expects($this->any())->method('changelogFile')->willReturn('CHANGELOG.md');
    }

    public function createEvent(string $version = '1.2.3'): RemoveChangelogVersionEvent
    {
        return new RemoveChangelogVersionEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            $version
        );
    }

    public function testEventImplementsPackageEvent(): RemoveChangelogVersionEvent
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(EventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testEventImplementsChangelogAwareEvent(RemoveChangelogVersionEvent $event)
    {
        $this->assertInstanceOf(ChangelogEntryAwareEventInterface::class, $event);
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testPropagationIsNotStoppedByDefault(RemoveChangelogVersionEvent $event)
    {
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testIsNotFailedByDefault(RemoveChangelogVersionEvent $event)
    {
        $this->assertFalse($event->failed());
    }

    public function testAbortEmitsOutputAndStopsPropagationWithoutFailure()
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Aborting at user request'));

        $event = $this->createEvent();

        $this->assertNull($event->abort());
        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingOfVersionRemovalEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Removed changelog version 2.1.0 from file CHANGELOG.md'));

        $event = $this->createEvent('2.1.0');
        $event->discoveredConfiguration($this->config);

        $this->assertNull($event->versionRemoved());
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }
}
