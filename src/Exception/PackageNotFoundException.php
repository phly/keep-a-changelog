<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

class PackageNotFoundException extends RuntimeException
{
    public static function at(string $path) : self
    {
        return new self(sprintf(
            'composer.json file at %s does not contain a package name',
            $path
        ));
    }
}
