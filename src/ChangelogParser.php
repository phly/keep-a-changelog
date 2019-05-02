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
