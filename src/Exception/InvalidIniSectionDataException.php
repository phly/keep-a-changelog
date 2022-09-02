<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use InvalidArgumentException;

use function sprintf;

class InvalidIniSectionDataException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forSection(string $name): self
    {
        return new self(sprintf(
            'Invalid INI section %s; value must be an array of data',
            $name
        ));
    }
}
