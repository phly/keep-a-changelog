<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\DiscoverChangelogEntryListener;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DiscoverChangelogEntryListenerTest extends TestCase
{
    public function setUp()
    {
        $voidReturn = function () {
        };

        $this->changelog = __DIR__ . '/../_files/CHANGELOG.md';

        $this->config = $this->prophesize(Config::class);
        $this->config->changelogFile()->willReturn($this->changelog);

        $this->event = $this->prophesize(ChangelogEntryAwareEventInterface::class);
        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function testNotifiesEventWhenEntryNotFound()
    {
        $this->event->version()->willReturn('7.6.5');
        $this->event
            ->changelogEntryNotFound($this->changelog, '7.6.5')
            ->shouldBeCalled();

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->discoveredChangelogEntry(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventWithDiscoveredEntryOnSuccess()
    {
        $this->event->version()->willReturn('1.1.0');
        $expected = <<<'EOC'
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
        $this->event
            ->discoveredChangelogEntry(Argument::that(function ($entry) use ($expected) {
                TestCase::assertSame(26, $entry->index);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return $entry;
            }))
            ->shouldBeCalled();

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->changelogEntryNotFound(Argument::any())->shouldNotHaveBeenCalled();
    }
}
