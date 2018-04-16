<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

class ChangelogBump
{
    // @codingStandardsIgnoreStart
    private const CHANGELOG_LINE_REGEX = '/^\#\# (?<version>\d+\.\d+\.\d+(?:(?:alpha|beta|rc|dev|a|b)\d+)?) - (?:TBD|\d{4}-\d{2}-\d{2})$/m';
    // @codingStandardsIgnoreEnd

    private const TEMPLATE = <<< 'EOT'


## %s - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.


EOT;

    /** @var string */
    private $changelogFile;

    public function __construct(string $changelogFile)
    {
        $this->changelogFile = $changelogFile;
    }

    /**
     * @throws Exception\ChangelogEntriesNotFoundException
     */
    public function findLatestVersion() : string
    {
        $changelog = file_get_contents($this->changelogFile);

        if (! preg_match(self::CHANGELOG_LINE_REGEX, $changelog, $matches)) {
            throw Exception\ChangelogEntriesNotFoundException::forFile($this->changelogFile);
        }

        return $matches['version'];
    }

    public function bumpBugfixVersion(string $version) : string
    {
        [$major, $minor, $bugfix] = $this->parseVersion($version);
        $bugfix = (int) $bugfix;
        $bugfix += 1;
        return sprintf('%d.%d.%d', $major, $minor, $bugfix);
    }

    public function bumpMinorVersion(string $version) : string
    {
        [$major, $minor, $bugfix] = $this->parseVersion($version);
        $minor = (int) $minor;
        $minor += 1;
        return sprintf('%d.%d.0', $major, $minor);
    }

    public function bumpMajorVersion(string $version) : string
    {
        [$major, $minor, $bugfix] = $this->parseVersion($version);
        $major = (int) $major;
        $major += 1;
        return sprintf('%d.0.0', $major);
    }

    /**
     * Update the CHANGELOG with the new version information.
     *
     * @param string $version
     */
    public function updateChangelog(string $version)
    {
        $changelog = sprintf(self::TEMPLATE, $version);
        $contents = file_get_contents($this->changelogFile);
        $contents = preg_replace(
            "/^(\# Changelog\n\n.*?)(\n\n\#\# )/s",
            '$1' .  $changelog . '## ',
            $contents
        );
        file_put_contents($this->changelogFile, $contents);
    }

    private function parseVersion(string $version) : array
    {
        $base = preg_replace('/^(\d+\.\d+\.\d+).*$/', '$1', $version);
        return explode('.', $base, 3);
    }
}
