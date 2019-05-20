<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

/**
 * Provides an implementation of the changelogFileIsUnreadable() method
 * defined in the ConfigurableEventInterface.
 *
 * This trait assumes that the event will have a `$failed` flag for stopping
 * propagation/marking failure.
 */
trait ChangelogFileIsUnreadableTrait
{
    public function changelogFileIsUnreadable(string $changelogFile) : void
    {
        $this->failed = true;
        $this->output()->writeln(sprintf(
            '<error>Changelog file "%s" is unreadable.</error>',
            $changelogFile
        ));
    }
}
