<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Milestone\AbstractMilestoneProviderEvent;
use Phly\KeepAChangelog\Milestone\VerifyProviderListener;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PhlyTest\KeepAChangelog\TestAsset\AbstractMilestoneAwareProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VerifyProviderListenerTest extends TestCase
{
    private Config|MockObject $config;
    private AbstractMilestoneProviderEvent&MockObject $event;
    private VerifyProviderListener $listener;
    private ProviderSpec&MockObject $providerSpec;

    public function setUp(): void
    {
        $this->providerSpec = $this->createMock(ProviderSpec::class);
        $this->config       = $this->createMock(Config::class);
        $this->event        = $this->createMock(AbstractMilestoneProviderEvent::class);
        $this->listener     = new VerifyProviderListener();

        $this->config->expects($this->any())->method('provider')->willReturn($this->providerSpec);
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testListenerMarksEventAsIncompleteIfProviderSpecIsIncomplete(): void
    {
        $this->providerSpec->expects($this->once())->method('isComplete')->willReturn(false);
        $this->providerSpec->expects($this->never())->method('createProvider');
        $this->event->expects($this->once())->method('providerIsIncomplete');
        $this->event->expects($this->never())->method('discoveredProvider');

        $this->assertNull($this->listener->__invoke($this->event));
    }

    public function testListenerMarksEventInvalidIfProviderIsNotMilestoneAware(): void
    {
        $provider = $this->createMock(ProviderInterface::class);

        $this->providerSpec->expects($this->once())->method('isComplete')->willReturn(true);
        $this->providerSpec->expects($this->once())->method('createProvider')->willReturn($provider);
        $this->event->expects($this->once())->method('providerIncapableOfMilestones');

        $this->assertNull($this->listener->__invoke($this->event));
    }

    public function testListenerTellsEventProviderIsDiscoveredWhenProviderSpecProvideMilestoneAwareProvider(): void
    {
        $provider = $this->createMock(AbstractMilestoneAwareProvider::class);

        $this->providerSpec->expects($this->once())->method('isComplete')->willReturn(true);
        $this->providerSpec->expects($this->once())->method('createProvider')->willReturn($provider);
        $this->event->expects($this->never())->method('providerIsIncomplete');
        $this->event->expects($this->once())->method('discoveredProvider')->with($provider);

        $this->assertNull($this->listener->__invoke($this->event));
    }
}
