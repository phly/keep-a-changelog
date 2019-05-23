<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Edit;

use Phly\KeepAChangelog\ChangelogEditor;
use Phly\KeepAChangelog\Common\Editor;

use function file_get_contents;
use function file_put_contents;
use function proc_close;
use function proc_open;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;

class EditChangelogEntryListener
{
    public function __invoke(EditChangelogEntryEvent $event) : void
    {
        $changelogEntry = $event->changelogEntry();

        $status = (new Editor())->spawnEditor(
            $event->output(),
            $event->editor(),
            $this->createTempFileWithContents(
                $changelogEntry->contents
            )
        );

        if (0 !== $status) {
            $event->editorFailed();
            return;
        }

        (new ChangelogEditor())->update(
            $event->changelogFile(),
            file_get_contents($tempFile),
            $changelogEvent->index,
            $changelogEvent->length
        );

        $event->editComplete();
    }

    /**
     * Creates a temporary file with the changelog contents.
     */
    private function createTempFileWithContents(string $contents) : string
    {
        $filename = sprintf('%s.md', uniqid('KAC', true));
        $path     = sprintf('%s/%s', sys_get_temp_dir(), $filename);
        file_put_contents($path, $contents);
        return $path;
    }
}
