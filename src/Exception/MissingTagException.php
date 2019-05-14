<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;
use Throwable;

class MissingTagException extends RuntimeException
{
    public static function forVersion(string $version) : self
    {
        return new self(sprintf(
            'No tag found by the name %s found in current directory.',
            $version
        ));
    }

    public static function forPackageOnGithub(string $package, string $version, Throwable $e) : self
    {
        return new self(sprintf(
            'When verifying that the tag %s for package %s is present on GitHub,'
            . ' no corresponding tag was found',
            $version,
            $package
        ), $e->getCode(), $e);
    }

    public static function forTagOnGithub(string $package, string $version, Throwable $e) : self
    {
        return new self(sprintf(
            'When verifying that the tag %s for package %s is present on GitHub,'
            . ' an error occurred fetching tag details: %s',
            $version,
            $package,
            $e->getMessage()
        ), $e->getCode(), $e);
    }

    public static function forUnverifiedTagOnGithub(string $package, string $version) : self
    {
        return new self(sprintf(
            'When verifying that the tag %s for package %s is present on GitHub,'
            . ' the tag found was unsigned. Please recreate the tag using the'
            . ' -s flag.',
            $version,
            $package
        ));
    }
}
