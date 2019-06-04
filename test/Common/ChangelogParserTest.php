<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogParser;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function is_string;
use function iterator_to_array;

class ChangelogParserTest extends TestCase
{
    public function setUp()
    {
        $this->changelog = file_get_contents(__DIR__ . '/../_files/CHANGELOG.md');
        $this->parser    = new ChangelogParser();
    }

    public function testRaisesExceptionIfNoMatchingEntryForVersionFound()
    {
        $this->expectException(Exception\ChangelogNotFoundException::class);
        $this->parser->findChangelogForVersion($this->changelog, '3.0.0');
    }

    public function testRaisesExceptionIfMatchingEntryFoundButInvalidDateFormatSet()
    {
        $changelogWithInvalidReleaseDate = file_get_contents(__DIR__ . '/../_files/CHANGELOG-INVALID-DATE.md');
        $this->expectException(Exception\ChangelogMissingDateException::class);
        $this->parser->findChangelogForVersion($changelogWithInvalidReleaseDate, '1.1.0');
    }

    public function testRaisesExceptionIfUnableToIsolateChangelog()
    {
        $this->expectException(Exception\InvalidChangelogFormatException::class);
        $this->parser->findChangelogForVersion(file_get_contents(__DIR__ . '/../_files/CHANGELOG-INVALID.md'), '0.1.0');
    }

    public function testReturnsDiscoveredChangelogWhenDiscovered()
    {
        $expected  = <<<'EOF'
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

EOF;
        $changelog = $this->parser->findChangelogForVersion($this->changelog, '1.1.0');

        $this->assertEquals($expected, $changelog);
    }

    public function testReturnsDiscoveredChangelogForUnreleasedVersionWhenDiscovered()
    {
        $expected  = <<<'EOF'
### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

EOF;
        $changelog = $this->parser->findChangelogForVersion($this->changelog, '2.0.0');

        $this->assertEquals($expected, $changelog);
    }

    public function testRecognizedSingleVersionChangelog()
    {
        $changelog = $this->parser->findChangelogForVersion(
            file_get_contents(__DIR__ . '/../_files/CHANGELOG-SINGLE-VERSION.md'),
            '0.1.0'
        );

        $this->assertTrue(is_string($changelog));
    }

    public function testRetrievingDateRaisesExceptionIfNoMatchingEntryForVersionFound()
    {
        $this->expectException(Exception\ChangelogNotFoundException::class);
        $this->parser->findReleaseDateForVersion($this->changelog, '3.0.0');
    }

    public function testRetrievingDateRaisesExceptionIfMatchingEntryFoundButInvalidDateFormatPresent()
    {
        $changelogWithInvalidReleaseDate = file_get_contents(__DIR__ . '/../_files/CHANGELOG-INVALID-DATE.md');
        $this->expectException(Exception\ChangelogMissingDateException::class);
        $this->parser->findReleaseDateForVersion($changelogWithInvalidReleaseDate, '1.1.0');
    }

    public function testCanRetrieveDateForReleasedVersions()
    {
        $date = $this->parser->findReleaseDateForVersion($this->changelog, '1.1.0');
        $this->assertSame('2018-03-23', $date);
    }

    public function testCanRetrieveDateForUnreleasedVersion()
    {
        $date = $this->parser->findReleaseDateForVersion($this->changelog, '2.0.0');
        $this->assertSame('TBD', $date);
    }

    public function testCanRetrieveInformationOnAllVersions()
    {
        $expected = [
            '2.0.0' => 'TBD',
            '1.1.0' => '2018-03-23',
            '0.1.0' => '2018-03-23',
        ];

        $actual = iterator_to_array($this->parser->findAllVersions(__DIR__ . '/../_files/CHANGELOG.md'));

        $this->assertSame($expected, $actual);
    }
}
