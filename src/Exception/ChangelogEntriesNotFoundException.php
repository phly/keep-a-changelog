<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

use function sprintf;

class ChangelogEntriesNotFoundException extends RuntimeException implements ExceptionInterface
{
    public static function forFile(string $changelogFile): self
    {
        return new self(sprintf(
            'Unable to find any changelog entries in file %s; is it formatted correctly?',
            $changelogFile
        ));
    }
}
