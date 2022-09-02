<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveChangelogVersionEventTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->config     = $this->prophesize(Config::class);
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->config->changelogFile()->willReturn('CHANGELOG.md');
        $this->output->writeln(Argument::type('string'))->willReturn(null);
    }

    public function createEvent(string $version = '1.2.3'): RemoveChangelogVersionEvent
    {
        return new RemoveChangelogVersionEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal(),
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
        $event = $this->createEvent();

        $this->assertNull($event->abort());
        $this->output
            ->writeln(Argument::containingString('Aborting at user request'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingOfVersionRemovalEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent('2.1.0');
        $event->discoveredConfiguration($this->config->reveal());

        $this->assertNull($event->versionRemoved());
        $this->output
            ->writeln(Argument::containingString('Removed changelog version 2.1.0 from file CHANGELOG.md'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }
}
