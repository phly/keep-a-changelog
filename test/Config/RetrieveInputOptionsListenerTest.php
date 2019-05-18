<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\ConfigDiscovery;
use Phly\KeepAChangelog\Config\Exception\InvalidProviderException;
use Phly\KeepAChangelog\Config\RetrieveInputOptionsListener;
use Phly\KeepAChangelog\Provider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetrieveInputOptionsListenerTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->event  = new ConfigDiscovery(
            $this->input->reveal(),
            $this->output->reveal()
        );
        $this->config = $this->event->config();
    }

    public function testRaisesExceptionSettingProviderFromProviderClassOptionWhenClassDoesNotExist()
    {
        $this->input->hasOption('provider-class')->willReturn(true);
        $this->input->getOption('provider-class')->willReturn(ThisClassDoesNotExist::class);
        $listener = new RetrieveInputOptionsListener();
        $this->expectException(InvalidProviderException::class);
        $listener($this->event);
    }

    public function testRaisesExceptionSettingProviderFromProviderOptionWhenNotInProviderList()
    {
        $this->input->hasOption('provider-class')->willReturn(false);
        $this->input->hasOption('provider')->willReturn(true);
        $this->input->getOption('provider')->willReturn('unknown-provider-type');
        $listener = new RetrieveInputOptionsListener();
        $this->expectException(InvalidProviderException::class);
        $listener($this->event);
    }

    public function testCanPopulateProviderFromProviderClassOption()
    {
        $this->input->hasOption('provider-class')->willReturn(true);
        $this->input->getOption('provider-class')->willReturn(Provider\GitLab::class);

        $this->input->hasOption('provider-token')->willReturn(false);
        $this->input->hasOption('provider-url')->willReturn(false);
        $this->input->hasOption('package')->willReturn(false);
        $this->input->hasOption('changelog')->willReturn(false);
        $this->input->hasOption('remote')->willReturn(false);

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $providerSpec = $this->config->provider();
        $this->assertInstanceOf(Provider\ProviderSpec::class, $providerSpec);
        $this->assertSame('--provider-class', $providerSpec->name());
        $this->assertInstanceOf(Provider\GitLab::class, $providerSpec->createProvider());
    }

    public function testCanPopulateProviderFromProviderOption()
    {
        $this->input->hasOption('provider-class')->willReturn(false);
        $this->input->hasOption('provider')->willReturn(true);
        $this->input->getOption('provider')->willReturn('gitlab');

        $this->input->hasOption('provider-token')->willReturn(true);
        $this->input->getOption('provider-token')->willReturn('this-is-the-token');

        $this->input->hasOption('provider-url')->willReturn(true);
        $this->input->getOption('provider-url')->willReturn('https://git.mwop.net');

        $this->input->hasOption('package')->willReturn(true);
        $this->input->getOption('package')->willReturn('some/package');

        $this->input->hasOption('changelog')->willReturn(false);
        $this->input->hasOption('remote')->willReturn(false);

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $providerSpec = $this->config->provider();
        $this->assertInstanceOf(Provider\ProviderSpec::class, $providerSpec);
        $this->assertSame('gitlab', $providerSpec->name());

        $provider = $providerSpec->createProvider();
        $this->assertInstanceOf(Provider\GitLab::class, $provider);
        $this->assertSame('git.mwop.net', $provider->domain());
        $this->assertTrue($provider->canCreateRelease()); // indicates both token and package are present
    }

    public function testCanPopulatePackageFromOption()
    {
        $this->input->hasOption('provider-class')->willReturn(false);
        $this->input->hasOption('provider')->willReturn(false);
        $this->input->hasOption('provider-token')->willReturn(false);
        $this->input->hasOption('provider-url')->willReturn(false);
        $this->input->hasOption('changelog')->willReturn(false);
        $this->input->hasOption('remote')->willReturn(false);

        $this->input->hasOption('package')->willReturn(true);
        $this->input->getOption('package')->willReturn('some/package');

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $this->assertSame('some/package', $this->config->package());
    }

    public function testCanPopulateChangelogFileFromOption()
    {
        $this->input->hasOption('provider-class')->willReturn(false);
        $this->input->hasOption('provider')->willReturn(false);
        $this->input->hasOption('provider-token')->willReturn(false);
        $this->input->hasOption('provider-url')->willReturn(false);
        $this->input->hasOption('package')->willReturn(false);
        $this->input->hasOption('remote')->willReturn(false);

        $this->input->hasOption('changelog')->willReturn(true);
        $this->input->getOption('changelog')->willReturn('changelog.txt');

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $this->assertSame('changelog.txt', $this->config->changelogFile());
    }

    public function testCanPopulateRemoteFromOption()
    {
        $this->input->hasOption('provider-class')->willReturn(false);
        $this->input->hasOption('provider')->willReturn(false);
        $this->input->hasOption('provider-token')->willReturn(false);
        $this->input->hasOption('provider-url')->willReturn(false);
        $this->input->hasOption('package')->willReturn(false);
        $this->input->hasOption('changelog')->willReturn(false);

        $this->input->hasOption('remote')->willReturn(true);
        $this->input->getOption('remote')->willReturn('upstream');

        $listener = new RetrieveInputOptionsListener();

        $this->assertNull($listener($this->event));

        $this->assertSame('upstream', $this->config->remote());
    }
}
