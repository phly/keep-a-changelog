<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Provider;

use Phly\KeepAChangelog\Provider\ProviderSpec;
use PhlyTest\KeepAChangelog\TestAsset\Provider;
use PHPUnit\Framework\TestCase;

class ProviderSpecTest extends TestCase
{
    public function isCompleteValues() : iterable
    {
        yield 'empty className' => ['', false];
        yield 'not a class className' => ['not-a-class', false];
        yield 'invalid className' => [self::class, false];
        yield 'valid className' => [Provider::class, true];
    }

    /**
     * @dataProvider isCompleteValues
     */
    public function testIsCompleteReturnsExpectedValue(string $className, bool $expectedValue)
    {
        $spec = new ProviderSpec('test');
        $spec->setClassName($className);
        $this->assertSame($expectedValue, $spec->isComplete());
        $this->assertSame('test', $spec->name());
    }

    public function testOnlyRequiresClassNameToCreateProvider()
    {
        $spec = new ProviderSpec('test');
        $spec->setClassName(Provider::class);

        $provider = $spec->createProvider();

        $this->assertSame('test', $spec->name());
        $this->assertInstanceOf(Provider::class, $provider);
        $this->assertNull($provider->package);
        $this->assertNull($provider->token);
        $this->assertNull($provider->url);
    }

    public function testSetsPackageInCreatedProviderWhenPresentInSpec()
    {
        $spec = new ProviderSpec('test');
        $spec->setClassName(Provider::class);
        $spec->setPackage('some/package');

        $provider = $spec->createProvider();

        $this->assertInstanceOf(Provider::class, $provider);
        $this->assertSame('some/package', $provider->package);
        $this->assertNull($provider->token);
        $this->assertNull($provider->url);
    }

    public function testSetsTokenInCreatedProviderWhenPresentInSpec()
    {
        $spec = new ProviderSpec('test');
        $spec->setClassName(Provider::class);
        $spec->setToken('some-token');

        $provider = $spec->createProvider();

        $this->assertInstanceOf(Provider::class, $provider);
        $this->assertNull($provider->package);
        $this->assertSame('some-token', $provider->token);
        $this->assertNull($provider->url);
    }

    public function testSetsUrlInCreatedProviderWhenPresentInSpec()
    {
        $spec = new ProviderSpec('test');
        $spec->setClassName(Provider::class);
        $spec->setUrl('https://mwop.net');

        $provider = $spec->createProvider();

        $this->assertInstanceOf(Provider::class, $provider);
        $this->assertNull($provider->package);
        $this->assertNull($provider->token);
        $this->assertSame('https://mwop.net', $provider->url);
    }
}
