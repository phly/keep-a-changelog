<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config\ConfigListener;

/**
 * Marshal configuration for the TagReleaseEvent
 *
 * Overrides the parent constructor to hardcode the flags for requiring the
 * package name and remote name.
 */
class TagCommandConfigListener extends ConfigListener
{
    public function __construct()
    {
        parent::__construct(
            $requiresPackageName = true,
            $requiresRemoteName  = false
        );
    }
}
