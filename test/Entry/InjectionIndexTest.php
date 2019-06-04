<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Entry\InjectionIndex;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnexpectedValueException;

class InjectionIndexTest extends TestCase
{
    public function testNewInstanceHasExpectedDefaults()
    {
        $index = new InjectionIndex();
        $this->assertNull($index->index);
        $this->assertSame(InjectionIndex::ACTION_NOT_FOUND, $index->type);
    }

    public function testRetrievingUnknownPropertyRaisesException()
    {
        $index = new InjectionIndex();
        $this->expectException(UnexpectedValueException::class);
        $index->unknownProperty;
    }

    public function testSettingUnknownPropertyRaisesException()
    {
        $index = new InjectionIndex();
        $this->expectException(UnexpectedValueException::class);
        $index->unknownProperty = 'foo';
    }

    public function settingUnexpectedTypeRaisesTypeError()
    {
        $index = new InjectionIndex();
        $this->expectException(TypeError::class);
        $index->type = 'foo';
    }

    public function testCanSetValidValues()
    {
        $index = new InjectionIndex();
        $index->index = 42;
        $index->type = InjectionIndex::ACTION_INJECT;

        $this->assertSame(42, $index->index);
        $this->assertSame(InjectionIndex::ACTION_INJECT, $index->type);
    }
}
