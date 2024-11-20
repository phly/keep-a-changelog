<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\ConfigDiscovery;
use Phly\KeepAChangelog\Config\Exception\InvalidProviderException;
use Phly\KeepAChangelog\Config\RetrieveInputOptionsListener;
use Phly\KeepAChangelog\Provider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetrieveInputOptionsListenerTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private ConfigDiscovery $event;
    private Config $config;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->event  = new ConfigDiscovery(
            $this->input,
            $this->output
        );
        $this->config = $this->event->config();
    }

    public function testRaisesExceptionSettingProviderFromProviderClassOptionWhenClassDoesNotExist()
    {
        $this->input
            ->expects($this->atLeastOnce())
            ->method('hasOption')
            ->with('provider-class')
            ->willReturn(true);
        $this->input
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with('provider-class')
            ->willReturn(ThisClassDoesNotExist::class);
        $listener = new RetrieveInputOptionsListener();
        $this->expectException(InvalidProviderException::class);
        $listener($this->event);
    }

    public function testRaisesExceptionSettingProviderFromProviderOptionWhenNotInProviderList()
    {
        $this->input
            ->expects($this->atLeast(2))
            ->method('hasOption')
            ->will($this->returnValueMap([
                ['provider-class', false],
                ['provider', true],
            ]));
        $this->input
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with('provider')
            ->willReturn('unknown-provider-type');
        $listener = new RetrieveInputOptionsListener();
        $this->expectException(InvalidProviderException::class);
        $listener($this->event);
    }

    public function testCanPopulateProviderFromProviderClassOption()
    {
        $this->input
            ->expects($this->atLeast(6))
            ->method('hasOption')
            ->will($this->returnValueMap([
                ['provider-class', true],
                ['provider-token', false],
                ['provider-url', false],
                ['package', false],
                ['changelog', false],
                ['remote', false],
            ]));
        $this->input
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with('provider-class')
            ->willReturn(Provider\GitLab::class);

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $providerSpec = $this->config->provider();
        $this->assertInstanceOf(Provider\ProviderSpec::class, $providerSpec);
        $this->assertSame('--provider-class', $providerSpec->name());
        $this->assertInstanceOf(Provider\GitLab::class, $providerSpec->createProvider());
    }

    public function testCanPopulateProviderFromProviderOption()
    {
        $this->input
            ->expects($this->atLeast(7))
            ->method('hasOption')
            ->will($this->returnValueMap([
                ['provider-class', false],
                ['provider', true],
                ['provider-token', true],
                ['provider-url', true],
                ['package', true],
                ['changelog', false],
                ['remote', false],
            ]));
        $this->input
            ->expects($this->atLeast(4))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['provider', 'gitlab'],
                ['provider-token', 'this-is-the-token'],
                ['provider-url', 'https://git.example.org'],
                ['package', 'some/package'],
            ]));

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $providerSpec = $this->config->provider();
        $this->assertInstanceOf(Provider\ProviderSpec::class, $providerSpec);
        $this->assertSame('gitlab', $providerSpec->name());

        $provider = $providerSpec->createProvider();
        $this->assertInstanceOf(Provider\GitLab::class, $provider);
        $this->assertTrue($provider->canCreateRelease()); // indicates both token and package are present
    }

    public function testCanPopulatePackageFromOption()
    {
        $this->input
            ->expects($this->atLeast(7))
            ->method('hasOption')
            ->will($this->returnValueMap([
                ['provider-class', false],
                ['provider', false],
                ['provider-token', false],
                ['provider-url', false],
                ['package', true],
                ['changelog', false],
                ['remote', false],
            ]));
        $this->input
            ->expects($this->once())
            ->method('getOption')
            ->with('package')
            ->willReturn('some/package');

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $this->assertSame('some/package', $this->config->package());
    }

    public function testCanPopulateChangelogFileFromOption()
    {
        $this->input
            ->expects($this->atLeast(7))
            ->method('hasOption')
            ->will($this->returnValueMap([
                ['provider-class', false],
                ['provider', false],
                ['provider-token', false],
                ['provider-url', false],
                ['package', false],
                ['changelog', true],
                ['remote', false],
            ]));
        $this->input
            ->expects($this->once())
            ->method('getOption')
            ->with('changelog')
            ->willReturn('changelog.txt');

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $this->assertSame('changelog.txt', $this->config->changelogFile());
    }

    public function testCanPopulateRemoteFromOption()
    {
        $this->input
            ->expects($this->atLeast(7))
            ->method('hasOption')
            ->will($this->returnValueMap([
                ['provider-class', false],
                ['provider', false],
                ['provider-token', false],
                ['provider-url', false],
                ['package', false],
                ['changelog', false],
                ['remote', true],
            ]));
        $this->input
            ->expects($this->once())
            ->method('getOption')
            ->with('remote')
            ->willReturn('upstream');

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $this->assertSame('upstream', $this->config->remote());
    }
}
