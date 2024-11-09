<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\EditChangelogLinksEvent;
use Phly\KeepAChangelog\Changelog\EditChangelogLinksListener;
use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\Editor;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

use function file_get_contents;

class EditChangelogLinksListenerTest extends TestCase
{
    private ChangelogEditor&MockObject $changelogEditor;
    private Config&MockObject $config;
    private Editor&MockObject $editor;
    private EditChangelogLinksEvent&MockObject $event;
    private OutputInterface&MockObject $output;
    private EditChangelogLinksListener $listener;

    protected function setUp(): void
    {
        $this->changelogEditor = $this->createMock(ChangelogEditor::class);
        $this->config          = $this->createMock(Config::class);
        $this->editor          = $this->createMock(Editor::class);
        $this->event           = $this->createMock(EditChangelogLinksEvent::class);
        $this->output          = $this->createMock(OutputInterface::class);

        $this->config->expects($this->any())->method('changelogFile')->willReturn('changelog.txt');
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
        $this->event->expects($this->any())->method('editor')->willReturn('vim');
        $this->event->expects($this->any())->method('output')->willReturn($this->output);

        $this->listener                  = new EditChangelogLinksListener();
        $this->listener->changelogEditor = $this->changelogEditor;
        $this->listener->editor          = $this->editor;
        $this->listener->mockTempFile    = __DIR__ . '/../_files/LINKS.md';
    }

    public function emptyContentOrLinks(): iterable
    {
        yield 'empty' => [null];

        $links           = new ChangelogEntry();
        $links->index    = 70;
        $links->length   = 5;
        $links->contents = <<<'EOC'
            [2.0.0]: https://example.org/diff/1.1.0...develop
            [1.1.0]: https://example.org/releases/1.1.0
            [1.0.1]: https://example.org/releases/1.1.1
            [1.0.0]: https://example.org/releases/1.1.0
            [0.1.0]: https://example.org/releases/0.1.0
            
            EOC;
        yield 'populated' => [$links];
    }

    /**
     * @dataProvider emptyContentOrLinks
     */
    public function testNotifiesEventOfEditorFailure(?ChangelogEntry $links)
    {
        $this->event->expects($this->atLeastOnce())->method('links')->willReturn($links);
        $this->editor
            ->expects($this->once())
            ->method('spawnEditor')
            ->with($this->output, 'vim', $this->listener->mockTempFile)
            ->willReturn(1);

        $this->event->expects($this->once())->method('editFailed')->with('changelog.txt');
        $this->event->expects($this->never())->method('editComplete');
        $this->changelogEditor->expects($this->never())->method('update');
        $this->changelogEditor->expects($this->never())->method('append');

        $this->assertNull(($this->listener)($this->event));
    }

    public function testAppendsChangelogFileWhenNoLinksPresent()
    {
        $this->event->expects($this->atLeastOnce())->method('links')->willReturn(null);
        $this->editor
            ->expects($this->once())
            ->method('spawnEditor')
            ->with($this->output, 'vim', $this->listener->mockTempFile)
            ->willReturn(0);

        $this->event->expects($this->once())->method('editComplete')->with('changelog.txt');
        $this->event->expects($this->never())->method('editFailed');
        $this->changelogEditor->expects($this->never())->method('update');
        $this->changelogEditor
            ->expects($this->once())
            ->method('append')
            ->with('changelog.txt', file_get_contents($this->listener->mockTempFile));

        $this->assertNull(($this->listener)($this->event));
    }

    public function testUpdatesChangelogFileWhenLinksPresent()
    {
        $links           = new ChangelogEntry();
        $links->index    = 70;
        $links->length   = 5;
        $links->contents = <<<'EOC'
            [2.0.0]: https://example.org/diff/1.1.0...develop
            [1.1.0]: https://example.org/releases/1.1.0
            [1.0.1]: https://example.org/releases/1.1.1
            [1.0.0]: https://example.org/releases/1.1.0
            [0.1.0]: https://example.org/releases/0.1.0
            
            EOC;

        $this->event->expects($this->atLeastOnce())->method('links')->willReturn($links);
        $this->editor
            ->expects($this->once())
            ->method('spawnEditor')
            ->with($this->output, 'vim', $this->listener->mockTempFile)
            ->willReturn(0);

        $this->event->expects($this->once())->method('editComplete')->with('changelog.txt');
        $this->event->expects($this->never())->method('editFailed');
        $this->changelogEditor->expects($this->never())->method('append');
        $this->changelogEditor
            ->expects($this->once())
            ->method('update')
            ->with('changelog.txt', file_get_contents($this->listener->mockTempFile), $links);

        $this->assertNull(($this->listener)($this->event));
    }
}
