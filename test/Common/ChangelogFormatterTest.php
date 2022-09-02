<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogFormatter;
use PHPUnit\Framework\TestCase;

class ChangelogFormatterTest extends TestCase
{
    public function testFormatsHeadingsForUseWithTags()
    {
        $changelog = <<<'EOC'
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

EOC;

        $expected = <<<'EOC'
Added
-----

- Nothing.

Changed
-------

- Nothing.

Deprecated
----------

- Nothing.

Removed
-------

- Nothing.

Fixed
-----

- Nothing.

EOC;

        $formatter = new ChangelogFormatter();

        $this->assertEquals($expected, $formatter->format($changelog));
    }
}
