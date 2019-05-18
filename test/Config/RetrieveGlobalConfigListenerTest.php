<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\ConfigDiscovery;
use Phly\KeepAChangelog\Config\RetrieveGlobalConfigListener;
use Phly\KeepAChangelog\Provider;
use PHPUnit\Framework\TestCase;

class RetrieveGlobalConfigListenerTest extends TestCase
{
    public function setUp()
    {
        $this->config = new Config();
        $this->event  = $this->prophesize(ConfigDiscovery::class);
        $this->event->config()->willReturn($this->config);
    }

    public function createListener() : RetrieveGlobalConfigListener
    {
        $listener = new RetrieveGlobalConfigListener();
        $listener->configRoot = realpath(__DIR__ . '/../_files/config');
        return $listener;
    }

    public function testPopulatesConfigFromFile()
    {
        $listener = $this->createListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->assertSame('changelog.txt', $this->config->changelogFile());
        $this->assertSame('upstream', $this->config->remote());

        $provider = $this->config->provider();
        $this->assertInstanceOf(Provider\ProviderSpec::class, $provider);
        $this->assertSame('gitlab', $provider->name());

        $this->assertAttributeSame('this-is-a-gitlab-token', 'token', $provider);

        $providers = $this->config->providers();
        $this->assertSame($provider, $providers->get('gitlab'));

        $provider  = $providers->get('github');
        $this->assertInstanceOf(Provider\ProviderSpec::class, $provider);
        $this->assertSame('github', $provider->name());
        $this->assertAttributeSame('https://github.mwop.net', 'url', $provider);
        $this->assertAttributeSame('this-is-a-github-token', 'token', $provider);
    }
}
