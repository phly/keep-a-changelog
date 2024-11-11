<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function realpath;

class RetrieveGlobalConfigListenerTest extends TestCase
{
    private Config $config;
    private ConfigDiscovery&MockObject $event;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->event  = $this->createMock(ConfigDiscovery::class);
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    /**
     * @return mixed
     */
    private function getAttributeValue(object $object, string $attribute)
    {
        $r = new ReflectionProperty($object, $attribute);
        $r->setAccessible(true);
        return $r->getValue($object);
    }

    public function createListener(): RetrieveGlobalConfigListener
    {
        $listener             = new RetrieveGlobalConfigListener();
        $listener->configRoot = realpath(__DIR__ . '/../_files/config');
        return $listener;
    }

    public function testPopulatesConfigFromFile()
    {
        $listener = $this->createListener();

        $this->assertNull($listener($this->event));

        $this->assertSame('changelog.txt', $this->config->changelogFile());
        $this->assertSame('upstream', $this->config->remote());

        $provider = $this->config->provider();
        $this->assertInstanceOf(Provider\ProviderSpec::class, $provider);
        $this->assertSame('gitlab', $provider->name());

        $this->assertSame('this-is-a-gitlab-token', $this->getAttributeValue($provider, 'token'));

        $providers = $this->config->providers();
        $this->assertSame($provider, $providers->get('gitlab'));

        $provider = $providers->get('github');
        $this->assertInstanceOf(Provider\ProviderSpec::class, $provider);
        $this->assertSame('github', $provider->name());
        $this->assertSame('https://github.mwop.net', $this->getAttributeValue($provider, 'url'));
        $this->assertSame('this-is-a-github-token', $this->getAttributeValue($provider, 'token'));
    }
}
