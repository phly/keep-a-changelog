<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider\GitLab;
use Phly\KeepAChangelog\Provider\ProviderList;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testBareInstanceHasExpectedDefaults()
    {
        $config = new Config();
        $this->assertSame('CHANGELOG.md', $config->changelogFile());
        $this->assertNull($config->package());
        $this->assertInstanceOf(ProviderSpec::class, $config->provider());
        $this->assertInstanceOf(ProviderList::class, $config->providers());
        $this->assertNull($config->remote());

        $provider = $config->provider();
        $this->assertSame('github', $provider->name());
        $this->assertTrue($provider->isComplete());

        $providers = $config->providers();
        $this->assertTrue($providers->has('github'));
        $this->assertTrue($providers->has('gitlab'));
        $this->assertSame($provider, $providers->get('github'));
    }

    public function testChangelogFileIsMutable()
    {
        $config = new Config();
        $config->setChangelogFile('changelog.txt');
        $this->assertSame('changelog.txt', $config->changelogFile());
    }

    public function testPackageIsMutableAndPushesPackageToConfiguredProvider()
    {
        $config = new Config();
        $config->setPackage('some/package');
        $this->assertSame('some/package', $config->package());

        $provider = $config->provider()->createProvider();
        $this->assertTrue($provider->canGenerateLinks());
    }

    public function testRemoteIsMutable()
    {
        $config = new Config();
        $config->setRemote('upstream');
        $this->assertSame('upstream', $config->remote());
    }

    public function testSettingProviderNameSetsProviderSpec()
    {
        $config = new Config();
        $config->setProviderName('gitlab');

        $this->assertSame('gitlab', $config->provider()->name());
    }

    public function testSettingProviderNameWhenPackageIsPresentAlsoPushesPackageToProviderSpec()
    {
        $config = new Config();
        $config->setPackage('some/package');
        $config->setProviderName('gitlab');

        $provider = $config->provider()->createProvider();
        $this->assertInstanceOf(GitLab::class, $provider);
        $this->assertTrue($provider->canGenerateLinks());
    }
}
