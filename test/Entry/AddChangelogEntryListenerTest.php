<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\AddChangelogEntryListener;
use Phly\KeepAChangelog\Entry\EntryTypes;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AddChangelogEntryListenerTest extends TestCase
{
    private const CHANGELOG_INITIAL_ENTRY = <<<'EOC'
## 1.2.3 - TBD

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

    private const CHANGELOG_WITH_ENTRIES = <<<'EOC'
## 1.2.3 - TBD

### Added

- Initial added entry

### Changed

- Initial changed entry

### Deprecated

- Initial deprecated entry

### Removed

- Initial removed entry

### Fixed

- Initial fixed entry

EOC;

    public function setUp()
    {
        $this->entry  = new ChangelogEntry();
        $this->config = $this->prophesize(Config::class);
        $this->editor = $this->prophesize(ChangelogEditor::class);
        $this->event  = $this->prophesize(AddChangelogEntryEvent::class);
        $this->event->changelogEntry()->willReturn($this->entry);
        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function testNotifiesEventOfInvalidType()
    {
        $this->event->entryType()->willReturn('unknown-type');
        $this->event->entryTypeIsInvalid()->shouldBeCalled();

        $listener = new AddChangelogEntryListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->changelogEntry()->shouldNotHaveBeenCalled();
        $this->event->config()->shouldNotHaveBeenCalled();
        $this->event->matchingEntryTypeNotFound();
        $this->event->entry()->shouldNotHaveBeenCalled();
        $this->event->addedChangelogEntry(Argument::any())->shouldNotHaveBeenCalled();
        $this->editor->update(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventWhenMatchingEntryTypeNotPresent()
    {
        $this->entry->contents = <<<'EOC'
## 2.0.0 - 2019-05-30

### Changed

- Changed some things.

### Deprecated

- Deprecated things we will remove in the future.

### Removed

- Removed things no longer used.

### Fixed

- Fixed bugs.

EOC;

        $this->event->entryType()->willReturn(EntryTypes::TYPE_ADDED);
        $this->event->matchingEntryTypeNotFound()->shouldBeCalled();

        $listener = new AddChangelogEntryListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->entryTypeIsInvalid()->shouldNotHaveBeenCalled();
        $this->event->changelogEntry()->shouldHaveBeenCalled();
        $this->event->config()->shouldNotHaveBeenCalled();
        $this->event->entry()->shouldNotHaveBeenCalled();
        $this->event->addedChangelogEntry(Argument::any())->shouldNotHaveBeenCalled();
        $this->editor->update(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function expectedResults() : iterable
    {
        // @phpcs:disable
        return [
            'added-initial-entry'      => [self::CHANGELOG_INITIAL_ENTRY, "/\n### Added\n\n- New entry\n\n###/s",                                    EntryTypes::TYPE_ADDED,      'New entry'],
            'added-inject-entry'       => [self::CHANGELOG_WITH_ENTRIES,  "/\n### Added\n\n- New entry\n\n- Initial added entry\n\n###/s",           EntryTypes::TYPE_ADDED,      'New entry'],
            'changed-initial-entry'    => [self::CHANGELOG_INITIAL_ENTRY, "/\n### Changed\n\n- New entry\n\n###/s",                                  EntryTypes::TYPE_CHANGED,    'New entry'],
            'changed-inject-entry'     => [self::CHANGELOG_WITH_ENTRIES,  "/\n### Changed\n\n- New entry\n\n- Initial changed entry\n\n###/s",       EntryTypes::TYPE_CHANGED,    'New entry'],
            'deprecated-initial-entry' => [self::CHANGELOG_INITIAL_ENTRY, "/\n### Deprecated\n\n- New entry\n\n###/s",                               EntryTypes::TYPE_DEPRECATED, 'New entry'],
            'deprecated-inject-entry'  => [self::CHANGELOG_WITH_ENTRIES,  "/\n### Deprecated\n\n- New entry\n\n- Initial deprecated entry\n\n###/s", EntryTypes::TYPE_DEPRECATED, 'New entry'],
            'removed-initial-entry'    => [self::CHANGELOG_INITIAL_ENTRY, "/\n### Removed\n\n- New entry\n\n###/s",                                  EntryTypes::TYPE_REMOVED,    'New entry'],
            'removed-inject-entry'     => [self::CHANGELOG_WITH_ENTRIES,  "/\n### Removed\n\n- New entry\n\n- Initial removed entry\n\n###/s",       EntryTypes::TYPE_REMOVED,    'New entry'],
            'fixed-initial-entry'      => [self::CHANGELOG_INITIAL_ENTRY, "/\n### Fixed\n\n- New entry\n/s",                                         EntryTypes::TYPE_FIXED,      'New entry'],
            'fixed-inject-entry'       => [self::CHANGELOG_WITH_ENTRIES,  "/\n### Fixed\n\n- New entry\n\n- Initial fixed entry\n/s",                EntryTypes::TYPE_FIXED,      'New entry'],
        ];
        // @phpcs:enable
    }

    /**
     * @dataProvider expectedResults
     */
    public function testInjectsChangelogAsExpected(
        string $initialChangelogEntry,
        string $regExpForExpectedResult,
        string $section,
        string $entry
    ) {
        $this->entry->contents = $initialChangelogEntry;
        $this->entry->index    = 4;
        $this->entry->length   = 22;

        $this->config->changelogFile()->willReturn('CHANGELOG.md');

        $this->editor
            ->update(
                'CHANGELOG.md',
                Argument::that(function ($newEntry) use ($regExpForExpectedResult) {
                    TestCase::assertRegExp($regExpForExpectedResult, $newEntry);
                    return $newEntry;
                }),
                $this->entry
            )
            ->shouldBeCalled();

        $this->event->entryType()->willReturn($section);
        $this->event->entry()->willReturn($entry);
        $this->event->addedChangelogEntry('CHANGELOG.md', $section)->shouldBeCalled();

        $listener         = new AddChangelogEntryListener();
        $listener->editor = $this->editor->reveal();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->entryTypeIsInvalid()->shouldNotHaveBeenCalled();
        $this->event->matchingEntryTypeNotFound()->shouldNotHaveBeenCalled();
    }

    public function testIndentsMultilineEntries()
    {
        $entry = <<<'EOH'
This is a multiline entry.
All lines after the first one
should be indented.
EOH;

        $this->entry->contents = self::CHANGELOG_INITIAL_ENTRY;
        $this->entry->index    = 4;
        $this->entry->length   = 22;

        $this->config->changelogFile()->willReturn('CHANGELOG.md');

        $this->editor
            ->update(
                'CHANGELOG.md',
                Argument::that(function ($newEntry) {
                    TestCase::assertRegExp("/\n### Added\n\n- This is a multiline entry.\n/s", $newEntry);
                    TestCase::assertRegExp('/^- This is a multiline entry.$/m', $newEntry);
                    TestCase::assertRegExp('/^  All lines after the first one$/m', $newEntry);
                    TestCase::assertRegExp('/^  should be indented.$/m', $newEntry);
                    return $newEntry;
                }),
                $this->entry
            )
            ->shouldBeCalled();

        $this->event->entryType()->willReturn(EntryTypes::TYPE_ADDED);
        $this->event->entry()->willReturn($entry);
        $this->event->addedChangelogEntry('CHANGELOG.md', EntryTypes::TYPE_ADDED)->shouldBeCalled();

        $listener         = new AddChangelogEntryListener();
        $listener->editor = $this->editor->reveal();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->entryTypeIsInvalid()->shouldNotHaveBeenCalled();
        $this->event->matchingEntryTypeNotFound()->shouldNotHaveBeenCalled();
    }
}
