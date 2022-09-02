<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\DiscoverChangelogEntryListener;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DiscoverChangelogEntryListenerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
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

    public function testOmitsLinksWhenReturningLastEntryInFile()
    {
        $expected  = <<<'EOC'
## [0.1.0] - 2018-03-23

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
        $changelog = __DIR__ . '/../_files/CHANGELOG-WITH-LINKS.md';

        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn($changelog);

        $event = $this->prophesize(ChangelogEntryAwareEventInterface::class);
        $event->config()->will([$config, 'reveal']);
        $event->version()->willReturn('0.1.0');

        $event
            ->discoveredChangelogEntry(Argument::that(function ($entry) use ($expected) {
                TestCase::assertSame(48, $entry->index);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return $entry;
            }))
            ->shouldBeCalled();

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($event->reveal()));
        $event->changelogEntryNotFound(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function unreleasedVersions(): iterable
    {
        yield 'null'       => [null];
        yield 'unreleased' => ['unreleased'];
    }

    /**
     * @dataProvider unreleasedVersions
     */
    public function testNotifiesEventWithDiscoveredEntryWhenUnreleasedSectionFound(?string $version): void
    {
        $changelog = __DIR__ . '/../_files/CHANGELOG-WITH-UNRELEASED-SECTION.md';

        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn($changelog);

        $event = $this->prophesize(ChangelogEntryAwareEventInterface::class);
        $event->config()->will([$config, 'reveal']);
        $event->version()->willReturn($version);

        $expected = <<<'EOC'
## Unreleased

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
        $event
            ->discoveredChangelogEntry(Argument::that(function ($entry) use ($expected) {
                TestCase::assertSame(4, $entry->index, $entry->contents);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return $entry;
            }))
            ->shouldBeCalled();

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($event->reveal()));
        $this->event->changelogEntryNotFound(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @dataProvider unreleasedVersions
     */
    public function testNotifiesEventWithDiscoveredEntryWhenLinkedUnreleasedSectionFound(?string $version): void
    {
        $changelog = __DIR__ . '/../_files/CHANGELOG-WITH-LINKED-UNRELEASED-SECTION.md';

        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn($changelog);

        $event = $this->prophesize(ChangelogEntryAwareEventInterface::class);
        $event->config()->will([$config, 'reveal']);
        $event->version()->willReturn($version);

        $expected = <<<'EOC'
## [Unreleased]

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
        $event
            ->discoveredChangelogEntry(Argument::that(function ($entry) use ($expected) {
                TestCase::assertSame(4, $entry->index, $entry->contents);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return $entry;
            }))
            ->shouldBeCalled();

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($event->reveal()));
        $this->event->changelogEntryNotFound(Argument::any())->shouldNotHaveBeenCalled();
    }
}
