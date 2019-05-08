<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\AddEntry;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function realpath;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const E_WARNING;

class AddEntryTest extends TestCase
{
    /** @var null|string name of temporary file used during testing */
    private $tempFile;

    public function tearDown()
    {
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testRaisesExceptionIfTypeIsUnknown()
    {
        $this->expectException(Exception\InvalidEntryTypeException::class);
        (new AddEntry())('bad-type', 'CHANGELOG.md', 'New entry to add');
    }

    public function testRaisesExceptionIfUnableToGetContentsFromFile()
    {
        $caught = false;
        set_error_handler(function ($errno, $errstr) {
            return true;
        }, E_WARNING);
        try {
            (new AddEntry())(AddEntry::TYPE_ADDED, __DIR__ . '/CHANGELOG.md', 'New entry to add');
        } catch (Exception\ChangelogFileNotFoundException $e) {
            $caught = $e;
        } finally {
            restore_error_handler();
        }
        $this->assertInstanceOf(Exception\ChangelogFileNotFoundException::class, $caught);
    }

    public function expectedResults()
    {
        // @phpcs:disable
        return [
            'added-initial-entry'      => ['CHANGELOG-INITIAL.md',            'CHANGELOG-ADDED-INITIAL-EXPECTED.md',      AddEntry::TYPE_ADDED,      'New entry'],
            'added-inject-entry'       => ['CHANGELOG-ADDED-INJECT.md',       'CHANGELOG-ADDED-INJECT-EXPECTED.md',       AddEntry::TYPE_ADDED,      'New entry'],
            'changed-initial-entry'    => ['CHANGELOG-INITIAL.md',            'CHANGELOG-CHANGED-INITIAL-EXPECTED.md',    AddEntry::TYPE_CHANGED,    'New entry'],
            'changed-inject-entry'     => ['CHANGELOG-CHANGED-INJECT.md',     'CHANGELOG-CHANGED-INJECT-EXPECTED.md',     AddEntry::TYPE_CHANGED,    'New entry'],
            'deprecated-initial-entry' => ['CHANGELOG-INITIAL.md',            'CHANGELOG-DEPRECATED-INITIAL-EXPECTED.md', AddEntry::TYPE_DEPRECATED, 'New entry'],
            'deprecated-inject-entry'  => ['CHANGELOG-DEPRECATED-INJECT.md',  'CHANGELOG-DEPRECATED-INJECT-EXPECTED.md',  AddEntry::TYPE_DEPRECATED, 'New entry'],
            'removed-initial-entry'    => ['CHANGELOG-INITIAL.md',            'CHANGELOG-REMOVED-INITIAL-EXPECTED.md',    AddEntry::TYPE_REMOVED,    'New entry'],
            'removed-inject-entry'     => ['CHANGELOG-REMOVED-INJECT.md',     'CHANGELOG-REMOVED-INJECT-EXPECTED.md',     AddEntry::TYPE_REMOVED,    'New entry'],
            'fixed-initial-entry'      => ['CHANGELOG-INITIAL.md',            'CHANGELOG-FIXED-INITIAL-EXPECTED.md',      AddEntry::TYPE_FIXED,      'New entry'],
            'fixed-inject-entry'       => ['CHANGELOG-FIXED-INJECT.md',       'CHANGELOG-FIXED-INJECT-EXPECTED.md',       AddEntry::TYPE_FIXED,      'New entry'],
        ];
        // @phpcs:enable
    }

    /**
     * @dataProvider expectedResults
     */
    public function testInjectsChangelogAsExpected(
        string $initialChangelogFile,
        string $expectedChangelogFile,
        string $section,
        string $entry
    ) {
        $initialChangelogFile = sprintf('%s/_files/%s', realpath(__DIR__), $initialChangelogFile);
        $expectedChangelogFile = sprintf('%s/_files/%s', realpath(__DIR__), $expectedChangelogFile);

        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($this->tempFile, file_get_contents($initialChangelogFile));
        (new AddEntry())($section, $this->tempFile, $entry);

        $this->assertFileEquals($expectedChangelogFile, $this->tempFile);
    }

    public function testIndentsMultilineEntries()
    {
        $initialChangelogFile = sprintf('%s/_files/CHANGELOG-INITIAL.md', realpath(__DIR__));
        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($this->tempFile, file_get_contents($initialChangelogFile));

        $entry = <<<'EOH'
This is a multiline entry.
All lines after the first one
should be indented.
EOH;

        (new AddEntry())(AddEntry::TYPE_ADDED, $this->tempFile, $entry);
        $this->assertFileEquals(__DIR__ . '/_files/CHANGELOG-MULTILINE-EXPECTED.md', $this->tempFile);
    }
}
