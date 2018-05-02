<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

class ChangelogFileNotFoundException extends RuntimeException
{
    public static function at(string $location) : self
    {
        return new self(sprintf(
            'Unable to find readable changelog at location %s; are you in the correct directory?',
            $location
        ));
    }
}
