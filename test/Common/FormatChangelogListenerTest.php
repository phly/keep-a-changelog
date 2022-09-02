<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogAwareEventInterface;
use Phly\KeepAChangelog\Common\FormatChangelogListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FormatChangelogListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testListenerFormatsProvidedChangelogAndPushesItToTheEvent()
    {
        $event     = $this->prophesize(ChangelogAwareEventInterface::class);
        $changelog = <<<'EOC'
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

        $expected = <<<'EOC'
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
        $event->updateChangelog($expected)->shouldBeCalled();

        $listener = new FormatChangelogListener();

        $this->assertNull($listener($event->reveal()));
    }
}
