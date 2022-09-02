<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditSpawnerTrait;
use Phly\KeepAChangelog\Common\EditSpawnerTrait;

use function file_get_contents;

class EditChangelogVersionListener
{
    use ChangelogEditSpawnerTrait;
    use EditSpawnerTrait;

    public function __invoke(EditChangelogVersionEvent $event): void
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
            $this->unlinkTempFile($tempFile);
            $event->editorFailed();
            return;
        }

        $this->getChangelogEditor()->update(
            $event->config()->changelogFile(),
            file_get_contents($tempFile),
            $changelogEntry
        );

        $this->unlinkTempFile($tempFile);
        $event->editComplete();
    }
}
