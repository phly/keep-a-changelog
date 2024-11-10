<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use Phly\KeepAChangelog\Version\VerifyProviderCanReleaseListener;
use PHPUnit\Framework\TestCase;

class VerifyProviderCanReleaseListenerTest extends TestCase
{
    public function testListenerNotifiesEventThatProviderIsIncompleteIfProviderIsNotComplete()
    {
        $providerSpec = $this->createMock(ProviderSpec::class);
        $providerSpec->expects($this->once())->method('isComplete')->willReturn(false);
        $providerSpec->expects($this->never())->method('createProvider');

        $config = $this->createMock(Config::class);
        $config->expects($this->atLeastOnce())->method('provider')->willReturn($providerSpec);

        $event = $this->createMock(ReleaseEvent::class);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->once())->method('providerIsIncomplete');
        $event->expects($this->never())->method('discoveredProvider');

        $listener = new VerifyProviderCanReleaseListener();

        $this->assertNull($listener($event));
    }

    public function testListenerNotifiesEventThatProviderIsIncompleteIfProviderCannotRelease()
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->once())->method('canCreateRelease')->willReturn(false);

        $providerSpec = $this->createMock(ProviderSpec::class);
        $providerSpec->expects($this->once())->method('isComplete')->willReturn(true);
        $providerSpec->expects($this->once())->method('createProvider')->willReturn($provider);

        $config = $this->createMock(Config::class);
        $config->expects($this->atLeastOnce())->method('provider')->willReturn($providerSpec);

        $event = $this->createMock(ReleaseEvent::class);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->once())->method('providerIsIncomplete');
        $event->expects($this->never())->method('discoveredProvider');

        $listener = new VerifyProviderCanReleaseListener();

        $this->assertNull($listener($event));
    }

    public function testListenerNotifiesEventWithProviderWhenValid()
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->once())->method('canCreateRelease')->willReturn(true);

        $providerSpec = $this->createMock(ProviderSpec::class);
        $providerSpec->expects($this->once())->method('isComplete')->willReturn(true);
        $providerSpec->expects($this->once())->method('createProvider')->willReturn($provider);

        $config = $this->createMock(Config::class);
        $config->expects($this->atLeastOnce())->method('provider')->willReturn($providerSpec);

        $event = $this->createMock(ReleaseEvent::class);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->never())->method('providerIsIncomplete');
        $event->expects($this->once())->method('discoveredProvider')->with($provider);

        $listener = new VerifyProviderCanReleaseListener();

        $this->assertNull($listener($event));
    }
}
