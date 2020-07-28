<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

use function sprintf;

class InvalidChangelogFormatException extends RuntimeException implements ExceptionInterface
{
    public static function forVersion(string $version): self
    {
        return new self(sprintf(
            'Changelog entry found for version %s, but it appears to be formatted incorrectly.',
            $version
        ));
    }
}
