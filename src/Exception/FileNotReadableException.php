<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

use function sprintf;

class FileNotReadableException extends RuntimeException implements ExceptionInterface
{
    public static function forFile(string $filename): self
    {
        return new self(sprintf(
            'File "%s" either does not exist or is not readable.',
            $filename
        ));
    }
}
