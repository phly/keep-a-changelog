<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Config\ConfigListener;

/**
 * Marshal configuration for the various milestone events
 *
 * Overrides the parent constructor to hardcode the flags for requiring the
 * package name and remote name.
 */
class CommandConfigListener extends ConfigListener
{
    public function __construct()
    {
        parent::__construct(
            $requiresPackageName = true,
            $requiresRemoteName  = true
        );
    }
}
