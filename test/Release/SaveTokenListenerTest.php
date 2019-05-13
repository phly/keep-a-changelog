<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\SaveTokenEvent;
use Phly\KeepAChangelog\Release\SaveTokenListener;
use PHPUnit\Framework\TestCase;

class SaveTokenListenerTest extends TestCase
{
    public function setUp()
    {
        $this->event = $this->prophesize(SaveTokenEvent::class);
        $this->event->token()->willReturn('this-is-the-token');
        $this->tokenFile = sprintf(
            '%s/.keep-a-changelog/token',
            getenv('HOME')
        );
    }

    public function testCreatesTokenDirectoryAndFileIfDirectoryDoesNotExist()
    {
        $listener = new SaveTokenListener();
        $listener->isDir = function (string $name) : bool {
            TestCase::assertSame(dirname($this->tokenFile), $name);
            return false;
        };
        $listener->mkdir = function (string $name, int $mask, bool $recursive) {
            TestCase::assertSame(dirname($this->tokenFile), $name);
            TestCase::assertSame(0700, $mask);
            TestCase::assertTrue($recursive);
        };
        $listener->filePutContents = function ($file, string $token) {
            TestCase::assertSame($this->tokenFile, $file);
            TestCase::assertSame('this-is-the-token', $token);
        };
        $listener->chmod = function ($file, int $mask) {
            TestCase::assertSame($this->tokenFile, $file);
            TestCase::assertSame(0600, $mask);
        };

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testWritesTokenFileInExistingDirectoryIfDirectoryExists()
    {
        $listener = new SaveTokenListener();
        $listener->isDir = function (string $name) : bool {
            TestCase::assertSame(dirname($this->tokenFile), $name);
            return true;
        };
        $listener->mkdir = function (string $name, int $mask, bool $recursive) {
            TestCase::fail('mkdir should not be called');
        };
        $listener->filePutContents = function ($file, string $token) {
            TestCase::assertSame($this->tokenFile, $file);
            TestCase::assertSame('this-is-the-token', $token);
        };
        $listener->chmod = function ($file, int $mask) {
            TestCase::assertSame($this->tokenFile, $file);
            TestCase::assertSame(0600, $mask);
        };

        $this->assertNull($listener($this->event->reveal()));
    }
}
