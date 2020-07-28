<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\AbstractMilestoneProviderEvent;
use Phly\KeepAChangelog\Milestone\VerifyProviderIsMilestoneAwareListener;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class VerifyProviderIsMilestoneAwareListenerTest extends TestCase
{
    /** @var AbstractMilestoneProviderEvent|ObjectProphecy */
    private $event;

    /** @var VerifyProviderIsMilestoneAwareListener */
    private $listener;

    public function setUp(): void
    {
        $this->event    = $this->prophesize(AbstractMilestoneProviderEvent::class);
        $this->listener = new VerifyProviderIsMilestoneAwareListener();
    }

    public function testListenerDoesNotAlterEventIfProviderIsMilestoneAware(): void
    {
        $provider = $this->prophesize(MilestoneAwareProviderInterface::class)
            ->willImplement(ProviderInterface::class)
            ->reveal();
        $this->event->provider()->willReturn($provider)->shouldBeCalled();
        $this->event->providerIncapableOfMilestones()->shouldNotBeCalled();

        $this->assertNull($this->listener->__invoke($this->event->reveal()));
    }

    public function testListenerMarksEventInvalidIfProviderIsNotMilestoneAware(): void
    {
        $this->event->provider()->willReturn(null)->shouldBeCalled();
        $this->event->providerIncapableOfMilestones()->shouldBeCalled();

        $this->assertNull($this->listener->__invoke($this->event->reveal()));
    }
}
