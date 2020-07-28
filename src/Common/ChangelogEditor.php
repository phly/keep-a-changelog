<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function array_splice;
use function file;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function sprintf;

class ChangelogEditor
{
    public function update(string $filename, string $replacement, ChangelogEntry $entry): void
    {
        $contents = file($filename);
        array_splice($contents, $entry->index, $entry->length, $replacement);
        file_put_contents($filename, implode('', $contents));
    }

    public function append(string $filename, string $contentsToAppend): void
    {
        $contents = file_get_contents($filename);
        file_put_contents(
            $filename,
            sprintf("%s\n%s", $contents, $contentsToAppend)
        );
    }
}
