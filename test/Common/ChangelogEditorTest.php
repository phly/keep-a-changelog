<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class ChangelogEditorTest extends TestCase
{
    /** @var null|string */
    private $tempFile;

    public function setUp()
    {
        $this->tempFile = null;
    }

    public function tearDown()
    {
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
        $this->tempFile = null;
    }

    public function createChangelog() : string
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'CAK');
        file_put_contents(
            $this->tempFile,
            file_get_contents(__DIR__ . '/../_files/CHANGELOG.md')
        );
        return $this->tempFile;
    }

    public function testReplacesSpecifiedContent()
    {
        $changelog = $this->createChangelog();

        $entry           = new ChangelogEntry();
        $entry->index    = 26;
        $entry->length   = 22;
        $entry->contents = <<<'EOC'
## 1.1.0 - 2018-03-23

### Added

- Added a new feature.

### Changed

- Made some changes.

### Deprecated

- Nothing was deprecated.

### Removed

- Nothing was removed.

### Fixed

- Fixed some bugs.

EOC;

        $newContents = "These are the new contents.\n";

        $editor = new ChangelogEditor();

        $this->assertNull($editor->update($changelog, $newContents, $entry));
        $this->assertFileEquals(
            __DIR__ . '/../_files/CHANGELOG-EDITOR-EXPECTED.md',
            $changelog
        );
    }

    public function testCanAppendContentToExistingFile()
    {
        $changelog      = $this->createChangelog();
        $origContents   = file_get_contents($changelog);
        $appendContents = "These are appended contents.\n";
        $editor         = new ChangelogEditor();

        $this->assertNull($editor->append($changelog, $appendContents));

        $contents = file_get_contents($changelog);
        $this->assertSame(sprintf("%s\n%s", $origContents, $appendContents), $contents);
    }
}
