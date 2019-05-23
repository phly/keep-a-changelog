<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\ChangelogEditor;

class RemoveChangelogVersionListener
{
    public function __invoke(RemoveChangelogVersionEvent $event) : void
    {
        $changelog = $event->changelogFile();
        $entry     = $event->changelogEntry();

        (new ChangelogEditor())->update($changelog, '', $entry);

        $event->versionRemoved();
    }
}
