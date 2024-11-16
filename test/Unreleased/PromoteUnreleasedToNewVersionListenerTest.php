<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use Phly\KeepAChangelog\Unreleased\PromoteUnreleasedToNewVersionListener;
use PHPUnit\Framework\TestCase;

class PromoteUnreleasedToNewVersionListenerTest extends TestCase
{
    public function testWritesChangelogEntry(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('changelogFile')->willReturn('changelog.txt');

        $entry           = new ChangelogEntry();
        $entry->contents = <<<'END'
            ## Unreleased

            ### Added

            - Nothing.

            ### Changed

            - Nothing.

            ### Removed

            - Nothing.

            ### Deprecated

            - Nothing.

            ### Fixed

            - Nothing.
            END;
        $entry->index    = 4;
        $entry->length   = 22;

        $event = $this->createMock(PromoteEvent::class);
        $event->expects($this->atLeastOnce())->method('changelogEntry')->willReturn($entry);
        $event->expects($this->atLeastOnce())->method('newVersion')->willReturn('2.5.0');
        $event->expects($this->atLeastOnce())->method('releaseDate')->willReturn('2020-07-16');
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->atLeastOnce())->method('changelogReady');

        $editor = $this->createMock(ChangelogEditor::class);
        $editor
            ->expects($this->once())
            ->method('update')
            ->with(
                'changelog.txt',
                $this->stringContains('## 2.5.0 - 2020-07-16'),
                $entry
            );

        $listener                  = new PromoteUnreleasedToNewVersionListener();
        $listener->changelogEditor = $editor;

        $this->assertNull($listener($event));
    }
}
