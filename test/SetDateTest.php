<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\Exception;
use Phly\KeepAChangelog\SetDate;
use PHPUnit\Framework\TestCase;

use function date;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function restore_error_handler;
use function set_error_handler;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const E_WARNING;

class SetDateTest extends TestCase
{
    /** @var null|string name of temporary file used during testing */
    private $tempFile;

    public function tearDown()
    {
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testRaisesExceptionWhenChangelogFileNotFound()
    {
        $caught = false;
        set_error_handler(function ($errno, $errstr) {
            return true;
        }, E_WARNING);
        try {
            (new SetDate())(__DIR__ . '/CHANGELOG.md', date('Y-m-d'));
        } catch (Exception\ChangelogFileNotFoundException $e) {
            $caught = $e;
        } finally {
            restore_error_handler();
        }
        $this->assertInstanceOf(Exception\ChangelogFileNotFoundException::class, $caught);
    }

    public function testRaisesExceptionIfNoMatchingChangelogDiscovered()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($this->tempFile, file_get_contents(__DIR__ . '/_files/invalid-composer/composer.json'));

        $this->expectException(Exception\NoMatchingChangelogDiscoveredException::class);
        (new SetDate())($this->tempFile, date('Y-m-d'));
    }

    public function testSetsDateForFirstChangelogEntry()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($this->tempFile, file_get_contents(__DIR__ . '/_files/CHANGELOG.md'));

        (new SetDate())($this->tempFile, '2018-04-12');
        $this->assertFileEquals(__DIR__ . '/_files/CHANGELOG-DATED-EXPECTED.md', $this->tempFile);
    }
}
