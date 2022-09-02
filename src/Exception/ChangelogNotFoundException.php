<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

use function sprintf;

class ChangelogNotFoundException extends RuntimeException implements ExceptionInterface
{
    public static function forVersion(string $version): self
    {
        return new self(sprintf(
            'Unable to find changelog entry matching version %s',
            $version
        ));
    }
}
