<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use InvalidArgumentException;
use Phly\KeepAChangelog\Entry\EntryTypes;

use function implode;
use function sprintf;

class InvalidNoteTypeException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forCommandName(string $command): self
    {
        return new self(sprintf(
            'Invalid command name "%s"; must be of form "namespace:type", where type is in [%s]',
            $command,
            implode(', ', EntryTypes::TYPES)
        ));
    }
}
