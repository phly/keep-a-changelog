<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\EditChangelogLinksEvent;
use Phly\KeepAChangelog\Changelog\EditChangelogLinksListener;
use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\Editor;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

use function file_get_contents;

class EditChangelogLinksListenerTest extends TestCase
{
    public function setUp()
    {
        $voidReturn            = function () {
        };
        $this->changelogEditor = $this->prophesize(ChangelogEditor::class);
        $this->config          = $this->prophesize(Config::class);
        $this->editor          = $this->prophesize(Editor::class);
        $this->event           = $this->prophesize(EditChangelogLinksEvent::class);
        $this->output          = $this->prophesize(OutputInterface::class);

        $this->changelogEditor->update(Argument::any(), Argument::any(), Argument::any())->will($voidReturn);
        $this->changelogEditor->append(Argument::any(), Argument::any())->will($voidReturn);

        $this->config->changelogFile()->willReturn('changelog.txt');

        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->editor()->willReturn('vim');
        $this->event->editComplete(Argument::any())->will($voidReturn);
        $this->event->editFailed(Argument::any())->will($voidReturn);
        $this->event->output()->will([$this->output, 'reveal']);

        $this->listener                  = new EditChangelogLinksListener();
        $this->listener->changelogEditor = $this->changelogEditor->reveal();
        $this->listener->editor          = $this->editor->reveal();
        $this->listener->mockTempFile    = __DIR__ . '/../_files/LINKS.md';
    }

    public function emptyContentOrLinks() : iterable
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
        $this->event->links()->willReturn($links);
        $this->editor
            ->spawnEditor(
                $this->output->reveal(),
                'vim',
                $this->listener->mockTempFile
            )
            ->willReturn(1);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->editFailed('changelog.txt')->shouldHaveBeenCalled();
        $this->event->editComplete(Argument::any())->shouldNotHaveBeenCalled();
        $this->changelogEditor->update(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
        $this->changelogEditor->append(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testAppendsChangelogFileWhenNoLinksPresent()
    {
        $this->event->links()->willReturn(null);
        $this->editor
            ->spawnEditor(
                $this->output->reveal(),
                'vim',
                $this->listener->mockTempFile
            )
            ->willReturn(0);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->editComplete('changelog.txt')->shouldHaveBeenCalled();
        $this->event->editFailed(Argument::any())->shouldNotHaveBeenCalled();
        $this->changelogEditor->update(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
        $this->changelogEditor
            ->append(
                'changelog.txt',
                file_get_contents($this->listener->mockTempFile)
            )
            ->shouldHaveBeenCalled();
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
        $this->event->links()->willReturn($links);
        $this->editor
            ->spawnEditor(
                $this->output->reveal(),
                'vim',
                $this->listener->mockTempFile
            )
            ->willReturn(0);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->editComplete('changelog.txt')->shouldHaveBeenCalled();
        $this->event->editFailed(Argument::any())->shouldNotHaveBeenCalled();
        $this->changelogEditor->append(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
        $this->changelogEditor
            ->update(
                'changelog.txt',
                file_get_contents($this->listener->mockTempFile),
                $links
            )
            ->shouldHaveBeenCalled();
    }
}
