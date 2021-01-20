<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Milestone\AbstractMilestoneProviderEvent;
use Phly\KeepAChangelog\Milestone\VerifyProviderListener;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class VerifyProviderListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var Config|ObjectProphecy */
    private $config;

    /** @var AbstractMilestoneProviderEvent|ObjectProphecy */
    private $event;

    /** @var VerifyProviderListener */
    private $listener;

    /** @var ProviderSpec|ObjectProphecy */
    private $providerSpec;

    public function setUp(): void
    {
        $this->providerSpec = $this->prophesize(ProviderSpec::class);

        $this->config = $this->prophesize(Config::class);
        $this->config->provider()->will([$this->providerSpec, 'reveal']);

        $this->event = $this->prophesize(AbstractMilestoneProviderEvent::class);
        $this->event->config()->will([$this->config, 'reveal']);

        $this->listener = new VerifyProviderListener();
    }

    public function testListenerMarksEventAsIncompleteIfProviderSpecIsIncomplete(): void
    {
        $this->providerSpec->isComplete()->willReturn(false);
        $this->providerSpec->createProvider()->shouldNotBeCalled();
        $this->event->providerIsIncomplete()->shouldBeCalled();
        $this->event->discoveredProvider(Argument::any())->shouldNotBeCalled();

        $this->assertNull($this->listener->__invoke($this->event->reveal()));
    }

    public function testListenerMarksEventInvalidIfProviderIsNotMilestoneAware(): void
    {
        $provider = $this->prophesize(ProviderInterface::class)->reveal();
        $this->providerSpec->isComplete()->willReturn(true)->shouldBeCalled();
        $this->providerSpec->createProvider()->willReturn($provider)->shouldBeCalled();

        $this->event->providerIncapableOfMilestones()->shouldBeCalled();

        $this->assertNull($this->listener->__invoke($this->event->reveal()));
    }

    public function testListenerTellsEventProviderIsDiscoveredWhenProviderSpecProvideMilestoneAwareProvider(): void
    {
        $provider = $this->prophesize(MilestoneAwareProviderInterface::class)
            ->willImplement(ProviderInterface::class)
            ->reveal();
        $this->providerSpec->isComplete()->willReturn(true);
        $this->event->providerIsIncomplete()->shouldNotBeCalled();
        $this->providerSpec->createProvider()->willReturn($provider)->shouldBeCalled();

        $this->event->discoveredProvider($provider)->shouldBeCalled();

        $this->assertNull($this->listener->__invoke($this->event->reveal()));
    }
}
