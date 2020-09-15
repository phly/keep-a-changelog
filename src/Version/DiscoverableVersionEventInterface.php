<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
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
