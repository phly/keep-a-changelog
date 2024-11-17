<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\AddChangelogEntryListener;
use Phly\KeepAChangelog\Entry\EntryTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    private ChangelogEntry $entry;
    private Config&MockObject $config;
    private ChangelogEditor&MockObject $editor;
    private AddChangelogEntryEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->entry  = new ChangelogEntry();
        $this->config = $this->createMock(Config::class);
        $this->editor = $this->createMock(ChangelogEditor::class);
        $this->event  = $this->createMock(AddChangelogEntryEvent::class);

        $this->event->expects($this->any())->method('changelogEntry')->willReturn($this->entry);
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testNotifiesEventOfInvalidType()
    {
        $this->event->expects($this->once())->method('entryType')->willReturn('unknown-type');
        $this->event->expects($this->once())->method('entryTypeIsInvalid');
        $this->event->expects($this->never())->method('changelogEntry');
        $this->event->expects($this->never())->method('config');
        $this->event->expects($this->any())->method('matchingEntryTypeNotFound');
        $this->event->expects($this->any())->method('entry');
        $this->event->expects($this->never())->method('addedChangelogEntry');
        $this->editor->expects($this->never())->method('update');

        $listener = new AddChangelogEntryListener();

        $this->assertNull($listener($this->event));
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

        $this->event->expects($this->once())->method('entryType')->willReturn(EntryTypes::TYPE_ADDED);
        $this->event->expects($this->never())->method('entryTypeIsInvalid');
        $this->event->expects($this->atLeastOnce())->method('changelogEntry');
        $this->event->expects($this->never())->method('config');
        $this->event->expects($this->atLeastOnce())->method('matchingEntryTypeNotFound');
        $this->event->expects($this->any())->method('entry');
        $this->event->expects($this->never())->method('addedChangelogEntry');
        $this->editor->expects($this->never())->method('update');

        $listener = new AddChangelogEntryListener();

        $this->assertNull($listener($this->event));
    }

    public function expectedResults(): iterable
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

        $this->config->expects($this->atLeastOnce())->method('changelogFile')->willReturn('CHANGELOG.md');

        $this->editor
            ->expects($this->once())
            ->method('update')
            ->with(
                'CHANGELOG.md',
                $this->matchesRegularExpression($regExpForExpectedResult),
                $this->entry
            );

        $this->event->expects($this->once())->method('entryType')->willReturn($section);
        $this->event->expects($this->atLeastOnce())->method('entry')->willReturn($entry);
        $this->event->expects($this->once())->method('addedChangelogEntry')->with('CHANGELOG.md', $section);
        $this->event->expects($this->never())->method('entryTypeIsInvalid');
        $this->event->expects($this->never())->method('matchingEntryTypeNotFound');

        $listener                  = new AddChangelogEntryListener();
        $listener->changelogEditor = $this->editor;

        $this->assertNull($listener($this->event));
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

        $this->config->expects($this->atLeastOnce())->method('changelogFile')->willReturn('CHANGELOG.md');

        $this->editor
            ->expects($this->once())
            ->method('update')
            ->with(
                'CHANGELOG.md',
                $this->callback(function (string $newEntry): bool {
                    TestCase::assertMatchesRegularExpression(
                        "/\n### Added\n\n- This is a multiline entry.\n/s",
                        $newEntry
                    );
                    TestCase::assertMatchesRegularExpression('/^- This is a multiline entry.$/m', $newEntry);
                    TestCase::assertMatchesRegularExpression('/^  All lines after the first one$/m', $newEntry);
                    TestCase::assertMatchesRegularExpression('/^  should be indented.$/m', $newEntry);
                    return true;
                }),
                $this->entry
            );

        $this->event->expects($this->once())->method('entryType')->willReturn(EntryTypes::TYPE_ADDED);
        $this->event->expects($this->atLeastOnce())->method('entry')->willReturn($entry);
        $this->event
            ->expects($this->once())
            ->method('addedChangelogEntry')
            ->with('CHANGELOG.md', EntryTypes::TYPE_ADDED);
        $this->event->expects($this->never())->method('entryTypeIsInvalid');
        $this->event->expects($this->never())->method('matchingEntryTypeNotFound');

        $listener                  = new AddChangelogEntryListener();
        $listener->changelogEditor = $this->editor;

        $this->assertNull($listener($this->event));
    }
}
