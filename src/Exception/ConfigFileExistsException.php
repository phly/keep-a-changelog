<?php
/**
 * @see       https://github.com/phly/keep-a-changelog-tagger for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog-tagger/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use RuntimeException;

class ConfigFileExistsException extends RuntimeException
{
    public static function forFile(string $file) : self
    {
        return new self(sprintf(
            'A config file already exists at %s; Use --overwrite to replace it.',
            $file
        ));
    }
}
