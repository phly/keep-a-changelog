<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogEntry;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnexpectedValueException;

class ChangelogEntryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->entry = new ChangelogEntry();
    }

    public function testDefaultsToEmptyValues()
    {
        $this->assertSame('', $this->entry->contents);
        $this->assertNull($this->entry->index);
        $this->assertSame(0, $this->entry->length);
    }

    public function testAttemptingToRetrieveUnknownPropertyRaisesUnknownValueException()
    {
        $this->expectException(UnexpectedValueException::class);
        $value = $this->entry->unknownProperty;
    }

    public function testAttemptingToSetUnknownPropertyRaisesUnknownValueException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->entry->unknownProperty = '';
    }

    public function invalidNumericValues(): iterable
    {
        yield 'false' => [false];
        yield 'true' => [true];
        yield 'zero-float' => [0.0];
        yield 'float' => [1.1];
        yield 'string' => ['string'];
        yield 'array' => [[1]];
        yield 'object' => [(object) ['value' => 1]];
    }

    public function invalidLengthValues(): iterable
    {
        yield 'null' => [null];
        yield from $this->invalidNumericValues();
    }

    /**
     * @dataProvider invalidNumericValues
     * @param mixed $value
     */
    public function testAttemptingToSetInvalidIndexValueRaisesTypeError($value)
    {
        $this->expectException(TypeError::class);
        $this->entry->index = $value;
    }

    /**
     * @dataProvider invalidLengthValues
     * @param mixed $value
     */
    public function testAttemptingToSetInvalidLengthValueRaisesTypeError($value)
    {
        $this->expectException(TypeError::class);
        $this->entry->length = $value;
    }

    public function invalidStringValues(): iterable
    {
        yield 'null' => [null];
        yield 'false' => [false];
        yield 'true' => [true];
        yield 'zero' => [0];
        yield 'int' => [1];
        yield 'zero-float' => [0.0];
        yield 'float' => [1.1];
        yield 'array' => [['string']];
        yield 'object' => [(object) ['value' => 'string']];
    }

    /**
     * @dataProvider invalidStringValues
     * @param mixed $value
     */
    public function testAttemptingToSetInvalidContentsValueRaisesTypeError($value)
    {
        $this->expectException(TypeError::class);
        $this->entry->contents = $value;
    }

    public function testAccessorsProxyToProperties(): void
    {
        $contents              = 'This is the contents';
        $length                = 1;
        $index                 = 0;
        $this->entry->contents = $contents;
        $this->entry->index    = $index;
        $this->entry->length   = $length;

        $this->assertSame($contents, $this->entry->contents());
        $this->assertSame($index, $this->entry->index());
        $this->assertSame($length, $this->entry->length());
    }
}
