<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Common\VersionAwareEventInterface;

interface DiscoverableVersionEventInterface extends
    EventInterface,
    VersionAwareEventInterface
{
    public function versionNotAccepted(): void;

    public function foundVersion(string $version): void;
}
