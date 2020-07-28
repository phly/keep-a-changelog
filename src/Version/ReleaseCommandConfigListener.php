<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config\ConfigListener;

/**
 * Marshal configuration for the ReleaseEvent
 *
 * Overrides the parent constructor to hardcode the flags for requiring the
 * package name and remote name.
 */
class ReleaseCommandConfigListener extends ConfigListener
{
    public function __construct()
    {
        parent::__construct(
            $requiresPackageName = true,
            $requiresRemoteName  = true
        );
    }
}
