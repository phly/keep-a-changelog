<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

/**
 * Compose this trait in any class that uses the ChangelogEditor to update or
 * append a changelog file.
 *
 * This trait allows mocking the ChangelogEditor during testing.
 */
trait ChangelogEditSpawnerTrait
{
    protected function getChangelogEditor() : ChangelogEditor
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
