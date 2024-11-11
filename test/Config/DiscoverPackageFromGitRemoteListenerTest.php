<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\DiscoverPackageFromGitRemoteListener;
use Phly\KeepAChangelog\Config\PackageNameDiscovery;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function preg_match;

class DiscoverPackageFromGitRemoteListenerTest extends TestCase
{
    private ProviderSpec&MockObject $provider;
    private Config&MockObject $config;
    private PackageNameDiscovery&MockObject $event;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ProviderSpec::class);
        $this->config   = $this->createMock(Config::class);
        $this->event    = $this->createMock(PackageNameDiscovery::class);
    }

    public function testReturnsEarlyWhenEventIndicatesPackageAlreadyDiscovered()
    {
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(true);
        $this->event->expects($this->never())->method('config');
        $this->event->expects($this->never())->method('foundPackage');

        $listener = new DiscoverPackageFromGitRemoteListener();

        $this->assertNull($listener($this->event));
    }

    public function testReturnsEarlyIfProviderHasNoUrl()
    {
        $this->provider->expects($this->once())->method('url')->willReturn('');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->never())->method('foundPackage');

        $listener = new DiscoverPackageFromGitRemoteListener();

        $this->assertNull($listener($this->event));
    }

    public function testDoesNotNotifyEventOfAnythingIfNoRemotesFound()
    {
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->never())->method('foundPackage');

        $listener       = new DiscoverPackageFromGitRemoteListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $return = 1;
        };

        $this->assertNull($listener($this->event));
    }

    public function testDoesNotNotifyEventOfAnythingIfNoRemoteUrlsFound()
    {
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->never())->method('foundPackage');

        $listener       = new DiscoverPackageFromGitRemoteListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            if ($command === 'git remote') {
                $return = 0;
                $output = ['origin', 'upstream'];
                return;
            }
            $return = 1;
        };

        $this->assertNull($listener($this->event));
    }

    public function testDoesNotNotifyEventOfAnythingIfNoMatchingRemotesFound()
    {
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->never())->method('foundPackage');

        $listener       = new DiscoverPackageFromGitRemoteListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $return = 0;
            if ($command === 'git remote') {
                $output = ['origin', 'upstream'];
                return;
            }
            if (preg_match('/origin/', $command)) {
                $output = ['git@github.com:some/package.git'];
                return;
            }
            $output = ['me@gitlab.com:another/package.git'];
        };

        $this->assertNull($listener($this->event));
    }

    public function testNotifiesEventOfFirstMatchingRemoteFound()
    {
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->once())->method('foundPackage')->with('some/package');

        $listener       = new DiscoverPackageFromGitRemoteListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $return = 0;
            if ($command === 'git remote') {
                $output = ['origin', 'upstream'];
                return;
            }
            if (preg_match('/origin/', $command)) {
                $output = ['me@git.mwop.net:some/package.git'];
                return;
            }
            $output = ['you@git.mwop.net:another/package.git'];
        };

        $this->assertNull($listener($this->event));
    }
}
