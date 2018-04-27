<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use InvalidArgumentException;

class InvalidPackageNameException extends InvalidArgumentException
{
    public static function forPackage(string $package) : self
    {
        return new self(sprintf(
            'Invalid package name "%s" either provided or discovered; must be in organization/repo format',
            $package
        ));
    }
}
