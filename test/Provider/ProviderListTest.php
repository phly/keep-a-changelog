<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Provider;

use Phly\KeepAChangelog\Provider\ProviderList;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PHPUnit\Framework\TestCase;

class ProviderListTest extends TestCase
{
    public function testListIsEmptyByDefault()
    {
        $list = new ProviderList();
        $this->assertEmpty($list->listKnownTypes());
    }

    public function testCanAddProviders()
    {
        $first  = new ProviderSpec('first');
        $second = new ProviderSpec('second');
        $third  = new ProviderSpec('third');

        $list = new ProviderList();
        $list->add($first);
        $list->add($second);
        $list->add($third);

        $this->assertTrue($list->has('first'));
        $this->assertTrue($list->has('second'));
        $this->assertTrue($list->has('third'));

        $this->assertSame($first, $list->get('first'));
        $this->assertSame($second, $list->get('second'));
        $this->assertSame($third, $list->get('third'));

        $this->assertSame(['first', 'second', 'third'], $list->listKnownTypes());
    }
}
