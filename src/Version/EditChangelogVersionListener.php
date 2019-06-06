<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\EditSpawnerTrait;

use function file_get_contents;

class EditChangelogVersionListener
{
    use EditSpawnerTrait;

    public function __invoke(EditChangelogVersionEvent $event) : void
    {
        $changelogEntry = $event->changelogEntry();
        $tempFile       = $this->createTempFileWithContents(
            $changelogEntry->contents
        );

        $status = $this->getEditor()->spawnEditor(
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
}
