<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Tag;

use Phly\KeepAChangelog\Config\ConfigListener as BaseConfigListener;

/**
 * Marshal configuration for the TagReleaseEvent
 *
 * Overrides the parent constructor to hardcode the flags for requiring the
 * package name and remote name.
 */
class ConfigListener extends BaseConfigListener
{
    public function __construct()
    {
        parent::__construct(
            $requiresPackageName = true,
            $requiresRemoteName = false
        );
    }
}
