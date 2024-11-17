<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\DiscoverRemoteFromGitRemotesListener;
use Phly\KeepAChangelog\Config\RemoteNameDiscovery;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiscoverRemoteFromGitRemotesListenerTest extends TestCase
{
    private ProviderSpec&MockObject $provider;
    private Config&MockObject $config;
    private RemoteNameDiscovery&MockObject $event;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ProviderSpec::class);
        $this->config   = $this->createMock(Config::class);
        $this->event    = $this->createMock(RemoteNameDiscovery::class);
    }

    public function testReturnsEarlyIfEventIndicatesRemoteWasAlreadyFound()
    {
        $this->config->expects($this->never())->method('provider');
        $this->event->expects($this->never())->method('config');
        $this->event->expects($this->never())->method('reportNoMatchingGitRemoteFound');
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(true);
        $this->event->expects($this->never())->method('foundRemote');
        $this->event->expects($this->never())->method('setRemotes');
        $this->provider->expects($this->never())->method('url');

        $listener = new DiscoverRemoteFromGitRemotesListener();

        $this->assertNull($listener($this->event));
    }

    public function testReturnsEarlyIfProviderHasNoUrl()
    {
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->never())->method('reportNoMatchingGitRemoteFound');
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);
        $this->event->expects($this->never())->method('foundRemote');
        $this->event->expects($this->never())->method('setRemotes');
        $this->provider->expects($this->once())->method('url')->willReturn('');

        $listener = new DiscoverRemoteFromGitRemotesListener();

        $this->assertNull($listener($this->event));
    }

    public function testReturnsEarlyIfConfigHasNoPackageAssociated()
    {
        $this->config->expects($this->once())->method('package')->willReturn(null);
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->never())->method('reportNoMatchingGitRemoteFound');
        $this->event->expects($this->never())->method('foundRemote');
        $this->event->expects($this->never())->method('setRemotes');
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');

        $listener = new DiscoverRemoteFromGitRemotesListener();

        $this->assertNull($listener($this->event));
    }

    public function testReportsNoMatchingGitRemotesFoundIfCommandFails()
    {
        $this->config->expects($this->once())->method('package')->willReturn('some/package');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);
        $this->event
            ->expects($this->once())
            ->method('reportNoMatchingGitRemoteFound')
            ->with('git.mwop.net', 'some/package');
        $this->event->expects($this->never())->method('foundRemote');
        $this->event->expects($this->never())->method('setRemotes');
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');

        $listener       = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $return = 1;
        };

        $this->assertNull($listener($this->event));
    }

    public function testReportsNoMatchingGitRemotesFoundIfNoRemotesMatchDomainAndPackageCombination()
    {
        $this->config->expects($this->once())->method('package')->willReturn('some/package');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);
        $this->event
            ->expects($this->once())
            ->method('reportNoMatchingGitRemoteFound')
            ->with('git.mwop.net', 'some/package');
        $this->event->expects($this->never())->method('foundRemote');
        $this->event->expects($this->never())->method('setRemotes');
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');

        $listener       = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $output = [
                'origin https://github.com/some/package.git (push)',
                'upstream me@gitlab.com:some/package.git (push)',
                'myself git://git.mwop.net/another/package.git (push)',
                'readonly git://git.mwop.net/some/package.git (pull)',
            ];
        };

        $this->assertNull($listener($this->event));
    }

    public function testReportsRemoteFoundIfExactlyOneRemoteMatches()
    {
        $this->config->expects($this->once())->method('package')->willReturn('some/package');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);
        $this->event
            ->expects($this->once())
            ->method('foundRemote')
            ->with('myself');
        $this->event->expects($this->never())->method('reportNoMatchingGitRemoteFound');
        $this->event->expects($this->never())->method('setRemotes');
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');

        $listener       = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $output = [
                'origin https://github.com/some/package.git (push)',
                'upstream me@gitlab.com:some/package.git (push)',
                'myself git://git.mwop.net/some/package.git (push)',
            ];
        };

        $this->assertNull($listener($this->event));
    }

    public function testReportsMultipleRemotesFoundIfMoreThanOneRemoteMatches()
    {
        $this->config->expects($this->once())->method('package')->willReturn('some/package');
        $this->config->expects($this->once())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->once())->method('config')->willReturn($this->config);
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);
        $this->event
            ->expects($this->once())
            ->method('setRemotes')
            ->with(['upstream', 'myself']);
        $this->event->expects($this->never())->method('reportNoMatchingGitRemoteFound');
        $this->event->expects($this->never())->method('foundRemote');
        $this->provider->expects($this->once())->method('url')->willReturn('https://git.mwop.net');

        $listener       = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $output = [
                'origin https://github.com/some/package.git (push)',
                'upstream me@git.mwop.net:some/package.git (push)',
                'myself git://git.mwop.net/some/package.git (push)',
            ];
        };

        $this->assertNull($listener($this->event));
    }
}
