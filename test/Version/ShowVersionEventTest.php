<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowVersionEventTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function createEvent(?string $version = null): ShowVersionEvent
    {
        return new ShowVersionEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal(),
            $version
        );
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
