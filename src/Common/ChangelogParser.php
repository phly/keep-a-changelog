<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Phly\KeepAChangelog\Exception;

use function fclose;
use function feof;
use function fgets;
use function file;
use function fopen;
use function preg_match;
use function preg_quote;
use function sprintf;

class ChangelogParser
{
    /**
     * @param string $changelogFile Changelog file to parse for versions.
     * @return iterable Where keys are the version entries, and values are the
     *     associated dates (either Y-m-d format, or the string 'TBD')
     */
    public function findAllVersions(string $changelogFile) : iterable
    {
        $versionRegex = sprintf(
            '/^%s %s - %s$/i',
            preg_quote('##', '/'),
            '(?P<version>\d+\.\d+\.\d+(?:(?:alpha|a|beta|b|rc|dev)\d+)?)',
            '(?P<date>(\d{4}-\d{2}-\d{2}|TBD))'
        );

        $linkedVersionRegex = sprintf(
            '/^%s %s - %s$/i',
            preg_quote('##', '/'),
            '\[(?P<version>\d+\.\d+\.\d+(?:(?:alpha|a|beta|b|rc|dev)\d+)?)\]',
            '(?P<date>(\d{4}-\d{2}-\d{2}|TBD))'
        );

        $fh = fopen($changelogFile, 'rb');

        while (! feof($fh)) {
            $line = fgets($fh);
            if (! $line) {
                continue;
            }

            if (preg_match($versionRegex, $line, $matches)) {
                yield $matches['version'] => $matches['date'];
                continue;
            }

            if (preg_match($linkedVersionRegex, $line, $matches)) {
                yield $matches['version'] => $matches['date'];
                continue;
            }
        }

        fclose($fh);
    }

    /**
     * @param bool $strict Set this to true to indicate that a date must be
     *                     provided for the version (and not "TBD").
     * @throws Exception\ChangelogNotFoundException
     * @throws Exception\ChangelogMissingDateException
     */
    public function findReleaseDateForVersion(string $changelog, string $version, bool $strict = false) : string
    {
        $regex = sprintf(
            '%s (?:\[%2$s\]|%2$s)',
            preg_quote('##', '/'),
            preg_quote($version, '/')
        );
        if (! preg_match('/^' . $regex . '/m', $changelog)) {
            throw Exception\ChangelogNotFoundException::forVersion($version);
        }

        $regex .= sprintf(
            ' - (?P<date>(\d{4}-\d{2}-\d{2}%s))',
            $strict ? '' : '|TBD'
        );
        if (! preg_match('/^' . $regex . '/m', $changelog, $matches)) {
            throw Exception\ChangelogMissingDateException::forVersion($version);
        }

        return $matches['date'];
    }

    /**
     * @throws Exception\ChangelogNotFoundException
     * @throws Exception\ChangelogMissingDateException
     * @throws Exception\InvalidChangelogFormatException
     */
    public function findChangelogForVersion(string $changelog, string $version) : string
    {
        $regex = sprintf(
            '%s (?:\[%2$s\]|%2$s)',
            preg_quote('##', '/'),
            preg_quote($version, '/')
        );
        if (! preg_match('/^' . $regex . '/m', $changelog)) {
            throw Exception\ChangelogNotFoundException::forVersion($version);
        }

        $regex .= ' - (\d{4}-\d{2}-\d{2}|TBD)';
        if (! preg_match('/^' . $regex . '/m', $changelog)) {
            throw Exception\ChangelogMissingDateException::forVersion($version);
        }

        $regex .= "\n\n(?P<changelog>.*?)(?=\n\#\# |\n\[.*?\]:\s*\S+|$)";
        if (! preg_match('/' . $regex . '/s', $changelog, $matches)) {
            throw Exception\InvalidChangelogFormatException::forVersion($version);
        }

        return $matches['changelog'];
    }

    public function findLinks(string $changelogFile) : ChangelogEntry
    {
        $regex      = '/^\[.*?]:\s*\S+$/';
        $linksEntry = new ChangelogEntry();
        $contents   = file($changelogFile) ?: [];
        foreach ($contents as $index => $line) {
            if (! preg_match($regex, $line)) {
                if ($linksEntry->index) {
                    break;
                }

                continue;
            }

            $linksEntry->contents .= $line;
            $linksEntry->length   += 1;
            if (! $linksEntry->index) {
                $linksEntry->index = $index;
            }
        }

        return $linksEntry;
    }
}
