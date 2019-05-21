<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use function array_splice;
use function file;
use function file_put_contents;
use function implode;

class ChangelogEditor
{
    public function update(string $filename, string $replacement, Common\ChangelogEntry $entry) : void
    {
        $contents = file($filename);
        array_splice($contents, $entry->index, $entry->length, $replacement);
        file_put_contents($filename, implode('', $contents));
    }
}
