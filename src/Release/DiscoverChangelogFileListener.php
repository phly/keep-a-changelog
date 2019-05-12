<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use function getcwd;
use function realpath;

class DiscoverChangelogFileListener
{
    public function __invoke(PrepareChangelogEvent $event) : void
    {
        $changelogFile = $event->input()->getOption('file')
            ?: realpath(getcwd()) . '/CHANGELOG.md';

        if (! is_readable($changelogFile)) {
            $event->changelogFileIsUnreadable($changelogFile);
            return;
        }

        $event->setChangelogFile($changelogFile);
    }
}
