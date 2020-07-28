<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

interface VersionAwareEventInterface
{
    /**
     * Compose this in events that either have the event injected, or can be
     * updated with the version.
     */
    public function version(): ?string;

    /**
     * Use this method to notify the event of an invalid version.
     *
     * This method should stop propagation.
     */
    public function versionIsInvalid(string $version): void;
}
