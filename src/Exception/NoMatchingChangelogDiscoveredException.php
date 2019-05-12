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

class NoMatchingChangelogDiscoveredException extends RuntimeException implements ExceptionInterface
{
    public static function for(string $changelogFile) : self
    {
        return new self(sprintf(
            'Unable to find un-dated changelog entry in %s',
            $changelogFile
        ));
    }
}
