<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionEvent;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveChangelogVersionListenerTest extends TestCase
{
    private Config&MockObject $config;
    private ChangelogEntry $entry;
    private ChangelogEditor&MockObject $editor;
    private RemoveChangelogVersionEvent&MockObject $event;
    private RemoveChangelogVersionListener $listener;

    protected function setUp(): void
    {
        $this->entry  = new ChangelogEntry();
        $this->config = $this->createMock(Config::class);
        $this->editor = $this->createMock(ChangelogEditor::class);
        $this->event  = $this->createMock(RemoveChangelogVersionEvent::class);

        $this->config->expects($this->any())->method('changelogFile')->willReturn('changelog.txt');

        $this->event->expects($this->any())->method('config')->willReturn($this->config);
        $this->event->expects($this->any())->method('changelogEntry')->willReturn($this->entry);

        $this->listener                  = new RemoveChangelogVersionListener();
        $this->listener->changelogEditor = $this->editor;
    }

    public function testUpdatesChangelogWithEmptyContentsForEntry()
    {
        $this->editor
            ->expects($this->once())
            ->method('update')
            ->with('changelog.txt', '', $this->entry);
        $this->event->expects($this->once())->method('versionRemoved');

        $this->assertNull(($this->listener)($this->event));
    }
}
