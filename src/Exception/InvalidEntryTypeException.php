<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use InvalidArgumentException;
use Phly\KeepAChangelog\AddEntry;

use function implode;
use function sprintf;

class InvalidEntryTypeException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forType(string $type) : self
    {
        return new self(sprintf(
            'Invalid changelog section type "%s" provided; must be one of [%s]',
            $type,
            implode(', ', AddEntry::TYPES)
        ));
    }
}
