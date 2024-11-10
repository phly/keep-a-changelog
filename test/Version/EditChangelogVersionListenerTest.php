<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\Editor;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\EditChangelogVersionEvent;
use Phly\KeepAChangelog\Version\EditChangelogVersionListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

use function file_get_contents;

class EditChangelogVersionListenerTest extends TestCase
{
    private ChangelogEditor&MockObject $changelogEditor;
    private Config&MockObject $config;
    private Editor&MockObject $editor;
    private ChangelogEntry $entry;
    private EditChangelogVersionEvent&MockObject $event;
    private EditChangelogVersionListener $listener;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->changelogEditor = $this->createMock(ChangelogEditor::class);
        $this->config          = $this->createMock(Config::class);
        $this->editor          = $this->createMock(Editor::class);
        $this->entry           = new ChangelogEntry();
        $this->event           = $this->createMock(EditChangelogVersionEvent::class);
        $this->output          = $this->createMock(OutputInterface::class);

        $this->config->expects($this->any())->method('changelogFile')->willReturn('changelog.txt');

        $this->event->expects($this->any())->method('changelogEntry')->willReturn($this->entry);
        $this->event->expects($this->any())->method('output')->willReturn($this->output);
        $this->event->expects($this->any())->method('editor')->willReturn('vim');
        $this->event->expects($this->any())->method('config')->willReturn($this->config);

        $this->listener                  = new EditChangelogVersionListener();
        $this->listener->editor          = $this->editor;
        $this->listener->changelogEditor = $this->changelogEditor;
        $this->listener->mockTempFile    = __DIR__ . '/../_files/CHANGELOG.md';
    }

    public function testMarksEventFailureWhenEditorFails()
    {
        $this->editor
            ->expects($this->once())
            ->method('spawnEditor')
            ->with(
                $this->output,
                'vim',
                $this->listener->mockTempFile
            )
            ->willReturn(1);

        $this->event->expects($this->once())->method('editorFailed');
        $this->event->expects($this->never())->method('editComplete');
        $this->event->expects($this->never())->method('config');
        $this->config->expects($this->never())->method('changelogFile');
        $this->changelogEditor->expects($this->never())->method('update');

        $this->assertNull(($this->listener)($this->event));
    }

    public function testNotifesEventOfEditorCompletion()
    {
        $this->editor
            ->expects($this->once())
            ->method('spawnEditor')
            ->with(
                $this->output,
                'vim',
                $this->listener->mockTempFile
            )
            ->willReturn(0);

        $this->event->expects($this->never())->method('editorFailed');
        $this->event->expects($this->once())->method('editComplete');
        $this->event->expects($this->once())->method('config');
        $this->config->expects($this->once())->method('changelogFile');
        $this->changelogEditor
            ->expects($this->once())
            ->method('update')
            ->with('changelog.txt', file_get_contents($this->listener->mockTempFile), $this->entry);

        $this->assertNull(($this->listener)($this->event));
    }
}
