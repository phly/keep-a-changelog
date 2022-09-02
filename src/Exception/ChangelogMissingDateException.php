<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

use function sprintf;

class ChangelogMissingDateException extends RuntimeException implements ExceptionInterface
{
    public static function forVersion(string $version): self
    {
        return new self(sprintf(
            'Changelog entry found for version %s, but it does not have a date set for it yet.',
            $version
        ));
    }
}
