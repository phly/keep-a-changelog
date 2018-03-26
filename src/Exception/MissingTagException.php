<?php
/**
 * @see       https://github.com/phly/keep-a-changelog-tagger for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog-tagger/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

class MissingTagException extends RuntimeException
{
    public static function forVersion(string $version) : self
    {
        return new self(sprintf(
            'No tag found by the name %s found in current directory.',
            $version
        ));
    }
}
