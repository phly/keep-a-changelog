<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\ChangelogFormatter;
use Phly\KeepAChangelog\Release\FormatChangelogListener;
use Phly\KeepAChangelog\Release\ReleaseEvent;
use PHPUnit\Framework\TestCase;

class FormatChangelogListenerTest extends TestCase
{
    public function testListenerFormatsProvidedChangelogAndPushesItToTheEvent()
    {
        $event = $this->prophesize(ReleaseEvent::class);
        $changelog = <<< 'EOC'
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

        $expected = <<< 'EOC'
Added
-----

- Added a new feature.

Changed
-------

- Made some changes.

Deprecated
----------

- Nothing was deprecated.

Removed
-------

- Nothing was removed.

Fixed
-----

- Fixed some bugs.

EOC;
        $event->changelog()->willReturn($changelog);
        $event->discoveredChangelog($expected)->shouldBeCalled();

        $listener = new FormatChangelogListener();

        $this->assertNull($listener($event->reveal()));
    }
}
