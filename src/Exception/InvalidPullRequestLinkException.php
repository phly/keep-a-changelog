<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

class InvalidPullRequestLinkException extends RuntimeException
{
    public static function forPackage(string $package, int $pr) : self
    {
        return new self(sprintf(
            'The pull request package %s has no PR %d',
            $package,
            $pr
        ));
    }

    public static function noValidLinks(int $pr) : self
    {
        return new self(sprintf(
            'No valid pull request link could be found for PR %d',
            $pr
        ));
    }
}
