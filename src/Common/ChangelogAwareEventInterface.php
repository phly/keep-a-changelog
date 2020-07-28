<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Throwable;

interface ChangelogAwareEventInterface extends
    EventInterface,
    VersionAwareEventInterface
{
    /**
     * Return the discovered changelog for the requested version.
     */
    public function changelog(): ?string;

    /**
     * Update the contents for the changelog for the requested version.
     */
    public function updateChangelog(string $changelog): void;

    /**
     * Notify the event of an error parsing the changelog file for the
     * requested version.
     *
     * This method should stop propagation.
     */
    public function errorParsingChangelog(string $changelogFile, Throwable $e);
}
