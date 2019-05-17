<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider\GitHub;
use Phly\KeepAChangelog\Provider\ProviderList;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testBareInstanceHasExpectedDefaults()
    {
        $config = new Config();
        $this->assertSame('CHANGELOG.md', $config->changelogFile());
        $this->assertNull($config->package());
        $this->assertInstanceOf(GitHub::class, $config->provider());
        $this->assertInstanceOf(ProviderList::class, $config->providers());
        $this->assertNull($config->remote());

        $provider = $config->provider();
        $this->assertFalse($provider->canCreateRelease());
        $this->assertFalse($provider->canGenerateLinks());

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

    public function testPackageIsMutable()
    {
        $config = new Config();
        $config->setPackage('some/package');
        $this->assertSame('some/package', $config->package());
    }

    public function testRemoteIsMutable()
    {
        $config = new Config();
        $config->setRemote('upstream');
        $this->assertSame('upstream', $config->remote());
    }
}
