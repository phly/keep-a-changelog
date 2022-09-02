<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

use function sprintf;

class MissingTagException extends RuntimeException implements ExceptionInterface
{
    public static function forVersion(string $version): self
    {
        return new self(sprintf(
            'No tag found by the name %s found in current directory.',
            $version
        ));
    }
}
