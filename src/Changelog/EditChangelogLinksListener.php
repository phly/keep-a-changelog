<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Common\ChangelogEditSpawnerTrait;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\EditSpawnerTrait;

use function file_get_contents;

class EditChangelogLinksListener
{
    use ChangelogEditSpawnerTrait;
    use EditSpawnerTrait;

    public function __invoke(EditChangelogLinksEvent $event): void
    {
        $changelog = $event->config()->changelogFile();
        $links     = $event->links();
        $contents  = $links instanceof ChangelogEntry ? $links->contents() : '';
        $tempFile  = $this->createTempFileWithContents($contents);

        $status = $this->getEditor()->spawnEditor(
            $event->output(),
            $event->editor(),
            $tempFile
        );

        if (0 !== $status) {
            $this->unlinkTempFile($tempFile);
            $event->editFailed($changelog);
            return;
        }

        $linkContents = file_get_contents($tempFile);
        $editor       = $this->getChangelogEditor();

        $links instanceof ChangelogEntry
            ? $editor->update($changelog, $linkContents, $links)
            : $editor->append($changelog, $linkContents);

        $this->unlinkTempFile($tempFile);
        $event->editComplete($changelog);
    }
}
