<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Remove;

use Phly\KeepAChangelog\ChangelogEditor;

class RemoveChangelogEntryListener
{
    public function __invoke(RemoveChangelogEntryEvent $event) : void
    {
        $changelog = $event->changelogFile();
        $entry     = $event->changelogEntry();

        (new ChangelogEditor())->update($changelog, '', $entry);

        $event->entryRemoved();
    }
}
