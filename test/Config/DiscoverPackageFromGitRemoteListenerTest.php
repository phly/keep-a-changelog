<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\DiscoverPackageFromGitRemoteListener;
use Phly\KeepAChangelog\Config\PackageNameDiscovery;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use function preg_match;

class DiscoverPackageFromGitRemoteListenerTest extends TestCase
{
    public function setUp()
    {
        $this->provider = $this->prophesize(ProviderSpec::class);
        $this->config   = $this->prophesize(Config::class);
        $this->event    = $this->prophesize(PackageNameDiscovery::class);
    }

    public function testReturnsEarlyWhenEventIndicatesPackageAlreadyDiscovered()
    {
        $this->event->packageWasFound()->willReturn(true);

        $listener = new DiscoverPackageFromGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->config()->shouldNotHaveBeenCalled();
        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReturnsEarlyIfProviderHasNoUrl()
    {
        $this->provider->url()->willReturn('');
        $this->config->provider()->will([$this->provider, 'reveal']);
        $this->event->packageWasFound()->willReturn(false);
        $this->event->config()->will([$this->config, 'reveal']);

        $listener = new DiscoverPackageFromGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testDoesNotNotifyEventOfAnythingIfNoRemotesFound()
    {
        $this->provider->url()->willReturn('https://git.mwop.net');
        $this->config->provider()->will([$this->provider, 'reveal']);
        $this->event->packageWasFound()->willReturn(false);
        $this->event->config()->will([$this->config, 'reveal']);

        $listener       = new DiscoverPackageFromGitRemoteListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $return = 1;
        };

        $this->assertNull($listener($this->event->reveal()));
        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testDoesNotNotifyEventOfAnythingIfNoRemoteUrlsFound()
    {
        $this->provider->url()->willReturn('https://git.mwop.net');
        $this->config->provider()->will([$this->provider, 'reveal']);
        $this->event->packageWasFound()->willReturn(false);
        $this->event->config()->will([$this->config, 'reveal']);

        $listener       = new DiscoverPackageFromGitRemoteListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            if ($command === 'git remote') {
                $return = 0;
                $output = ['origin', 'upstream'];
                return;
            }
            $return = 1;
        };

        $this->assertNull($listener($this->event->reveal()));
        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testDoesNotNotifyEventOfAnythingIfNoMatchingRemotesFound()
    {
        $this->provider->url()->willReturn('https://git.mwop.net');
        $this->config->provider()->will([$this->provider, 'reveal']);
        $this->event->packageWasFound()->willReturn(false);
        $this->event->config()->will([$this->config, 'reveal']);

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

        $this->assertNull($listener($this->event->reveal()));
        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventOfFirstMatchingRemoteFound()
    {
        $this->provider->url()->willReturn('https://git.mwop.net');
        $this->config->provider()->will([$this->provider, 'reveal']);
        $this->event->packageWasFound()->willReturn(false);
        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->foundPackage('some/package')->shouldBeCalled();

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

        $this->assertNull($listener($this->event->reveal()));
    }
}
