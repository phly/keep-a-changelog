<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\EditSpawnerTrait;

use function file_get_contents;

class EditChangelogLinksListener
{
    use EditSpawnerTrait;

    public function __invoke(EditChangelogLinksEvent $event) : void
    {
        $changelog = $event->config()->changelogFile();
        $links     = $event->links();
        $contents  = $links instanceof ChangelogEntry ? $links->contents : '';
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

        $this->unlinkTempFile($tempFile);

        $links instanceof ChangelogEntry
            ? $editor->update($changelog, $linkContents, $links)
            : $editor->append($changelog, $linkContents);

        $event->editComplete($changelog);
    }

    private function getChangelogEditor() : ChangelogEditor
    {
        if ($this->changelogEditor instanceof ChangelogEditor) {
            return $this->changelogEditor;
        }

        return new ChangelogEditor();
    }

    /**
     * Provide a ChangelogEditor instance to use.
     *
     * For testing purposes only.
     *
     * @internal
     * @var null|ChangelogEditor
     */
    public $changelogEditor;
}
