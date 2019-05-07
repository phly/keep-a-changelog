<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use stdClass;

trait ChangelogEditorTrait
{
    /**
     * Retrieves changelog entry from the file.
     *
     * If $version is null, it fetches the first entry; otherwise, it attempts
     * to fetch the entry associated with the given version.
     *
     * If no changelog entry is found, returns null. Otherwise, returns an
     * anonymous object with the keys:
     *
     * - index, indicating the line number where the contents began
     * - length, the number of lines in the contents
     * - contents, a string representing the changelog entry found in its entierty
     */
    private function getChangelogEntry($filename, ?string $version = null) : ?stdClass
    {
        $contents = file($filename);
        if (false === $contents) {
            throw Exception\ChangelogFileNotFoundException::at($filename);
        }

        $data = (object) [
            'contents' => '',
            'index' => null,
            'length' => 0,
        ];

        $boundaryRegex = '/^## \d+\.\d+\.\d+/';

        $regex = $version
            ? sprintf('/^## %s/', preg_quote($version))
            : $boundaryRegex;

        foreach ($contents as $index => $line) {
            if ($data->index && preg_match($boundaryRegex, $line)) {
                break;
            }

            if (preg_match($regex, $line)) {
                $data->contents = $line;
                $data->index = $index;
                $data->length = 1;
                continue;
            }

            if (! $data->index) {
                continue;
            }

            $data->contents .= $line;
            $data->length += 1;
        }

        return $data->index !== null ? $data : null;
    }

    private function updateChangelogEntry(string $filename, string $replacement, int $index, int $length)
    {
        $contents = file($filename);
        array_splice($contents, $index, $length, $replacement);
        file_put_contents($filename, implode('', $contents));
    }
}
