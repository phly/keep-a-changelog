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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

use function file_get_contents;

class EditChangelogVersionListenerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->voidReturn      = function () {
        };
        $this->changelogEditor = $this->prophesize(ChangelogEditor::class);
        $this->config          = $this->prophesize(Config::class);
        $this->editor          = $this->prophesize(Editor::class);
        $this->entry           = new ChangelogEntry();
        $this->event           = $this->prophesize(EditChangelogVersionEvent::class);
        $this->output          = $this->prophesize(OutputInterface::class);

        $this->changelogEditor->update(Argument::any(), Argument::any(), Argument::any())->will($this->voidReturn);

        $this->config->changelogFile()->willReturn('changelog.txt');

        $this->event->changelogEntry()->willReturn($this->entry);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->editor()->willReturn('vim');
        $this->event->editorFailed()->will($this->voidReturn);
        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->editComplete()->will($this->voidReturn);

        $this->listener                  = new EditChangelogVersionListener();
        $this->listener->editor          = $this->editor->reveal();
        $this->listener->changelogEditor = $this->changelogEditor->reveal();
        $this->listener->mockTempFile    = __DIR__ . '/../_files/CHANGELOG.md';
    }

    public function testMarksEventFailureWhenEditorFails()
    {
        $this->editor
            ->spawnEditor(
                Argument::that([$this->output, 'reveal']),
                'vim',
                $this->listener->mockTempFile
            )
            ->willReturn(1);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->editorFailed()->shouldHaveBeenCalled();
        $this->event->config()->shouldNotHaveBeenCalled();
        $this->event->editComplete()->shouldNotHaveBeenCalled();
        $this->config->changelogFile()->shouldNotHaveBeenCalled();
        $this->changelogEditor->update(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifesEventOfEditorCompletion()
    {
        $this->editor
            ->spawnEditor(
                Argument::that([$this->output, 'reveal']),
                'vim',
                $this->listener->mockTempFile
            )
            ->willReturn(0);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->editorFailed()->shouldNotHaveBeenCalled();
        $this->event->config()->shouldHaveBeenCalled();
        $this->event->editComplete()->shouldHaveBeenCalled();
        $this->config->changelogFile()->shouldHaveBeenCalled();
        $this->changelogEditor
            ->update(
                'changelog.txt',
                file_get_contents($this->listener->mockTempFile),
                $this->entry
            )
            ->shouldHaveBeenCalled();
    }
}
