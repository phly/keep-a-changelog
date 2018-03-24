<?php
/**
 * @see       https://github.com/phly/keep-a-changelog-tagger for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog-tagger/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use PHPUnit\Framework\TestCase;
use Phly\KeepAChangelog\ChangelogParser;
use Phly\KeepAChangelog\Exception;

class ChangelogParserTest extends TestCase
{
    public function setUp()
    {
        $this->changelog = file_get_contents(__DIR__ . '/_files/CHANGELOG.md');
        $this->parser = new ChangelogParser();
    }
    
    public function testRaisesExceptionIfNoMatchingEntryForVersionFound()
    {
        $this->expectException(Exception\ChangelogNotFoundException::class);
        $this->parser->findChangelogForVersion($this->changelog, '3.0.0');
    }

    public function testRaisesExceptionIfMatchingEntryFoundButDoesNotHaveDateSet()
    {
        $this->expectException(Exception\ChangelogMissingDateException::class);
        $this->parser->findChangelogForVersion($this->changelog, '2.0.0');
    }

    public function testRaisesExceptionIfUnableToIsolateChangelog()
    {
        $this->expectException(Exception\InvalidChangelogFormatException::class);
        $this->parser->findChangelogForVersion($this->changelog, '0.1.0');
    }

    public function testReturnsDiscoveredChangelogWhenDiscovered()
    {
        $expected = <<< 'EOF'
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
        $changelog = $this->parser->findChangelogForVersion($this->changelog, '1.1.0');

        $this->assertEquals($expected, $changelog);
    }
}
