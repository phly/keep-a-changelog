<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function preg_match;
use function sprintf;

class ValidateVersionListener
{
    public const PRE_RELEASE_REGEX = '-?(?:alpha|a|beta|b|rc|dev|patch|pl|p)\.?\d+';

    public function __invoke(VersionAwareEventInterface $event): void
    {
        $version = $event->version() ?: '';
        $pattern = sprintf('/^(\d+\.\d+\.\d+(%s)?|unreleased)$/i', self::PRE_RELEASE_REGEX);
        if (! preg_match($pattern, $version)) {
            $event->versionIsInvalid($version);
            return;
        }
    }
}
