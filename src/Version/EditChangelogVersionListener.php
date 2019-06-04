<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\Editor;

use function file_get_contents;
use function file_put_contents;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;

class EditChangelogVersionListener
{
    public function __invoke(EditChangelogVersionEvent $event) : void
    {
        $changelogEntry = $event->changelogEntry();
        $tempFile       = $this->createTempFileWithContents(
            $changelogEntry->contents
        );

        $status = (new Editor())->spawnEditor(
            $event->output(),
            $event->editor(),
            $tempFile
        );

        if (0 !== $status) {
            $event->editorFailed();
            return;
        }

        (new ChangelogEditor())->update(
            $event->config()->changelogFile(),
            file_get_contents($tempFile),
            $changelogEntry
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
