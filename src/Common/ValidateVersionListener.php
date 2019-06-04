<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function preg_match;

class ValidateVersionListener
{
    public function __invoke(VersionAwareEventInterface $event) : void
    {
        $version = $event->version() ?: '';
        if (! preg_match('/^\d+\.\d+\.\d+((?:alpha|a|beta|b|rc|dev|patch|pl|p)\d+)?$/i', $version)) {
            $event->versionIsInvalid($version);
            return;
        }
    }
}
