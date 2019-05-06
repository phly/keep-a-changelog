<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

class ChangelogParser
{
    /**
     * @param  string $changelogFile Changelog file to parse for versions.
     * @return iterable<string, string> where keys are the version entries,
     *     and values are the associated dates (either Y-m-d format, or the
     *     string 'TBD')
     */
    public function findAllVersions(string $changelogFile) : iterable
    {
        $fh = fopen($changelogFile, 'r');
        $regex = sprintf(
            '/^%s %s - %s$/i',
            preg_quote('##'),
            '(?P<version>\d+\.\d+\.\d+(?:(?:alpha|a|beta|b|rc|dev)\d+)?)',
            '(?P<date>(\d{4}-\d{2}-\d{2}|TBD))'
        );

        while (! feof($fh)) {
            $line = fgets($fh);
            if (! $line) {
                continue;
            }

            if (preg_match($regex, $line, $matches)) {
                yield $matches['version'] => $matches['date'];
            }
        }

        fclose($fh);
    }

    public function findReleaseDateForVersion(string $changelog, string $version) : string
    {
        $regex = preg_quote('## ' . $version);
        if (! preg_match('/^' . $regex . '/m', $changelog)) {
            throw Exception\ChangelogNotFoundException::forVersion($version);
        }

        $regex .= ' - (?P<date>(\d{4}-\d{2}-\d{2}|TBD))';
        if (! preg_match('/^' . $regex . '/m', $changelog, $matches)) {
            throw Exception\ChangelogMissingDateException::forVersion($version);
        }

        return $matches['date'];
    }

    public function findChangelogForVersion(string $changelog, string $version) : string
    {
        $regex = preg_quote('## ' . $version);
        if (! preg_match('/^' . $regex . '/m', $changelog)) {
            throw Exception\ChangelogNotFoundException::forVersion($version);
        }

        $regex .= ' - (\d{4}-\d{2}-\d{2}|TBD)';
        if (! preg_match('/^' . $regex . '/m', $changelog)) {
            throw Exception\ChangelogMissingDateException::forVersion($version);
        }

        $regex .= "\n\n(?P<changelog>.*?)(?=\n\#\# |$)";
        if (! preg_match('/' . $regex . '/s', $changelog, $matches)) {
            throw Exception\InvalidChangelogFormatException::forVersion($version);
        }

        return $matches['changelog'];
    }
}
