<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

/**
 * Create a new changelog file.
 */
class NewChangelog
{
    private const TEMPLATE = <<< 'EOT'
# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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

    public function __invoke(string $file, string $version) : void
    {
        $contents = sprintf(self::TEMPLATE, $version);
        file_put_contents($file, $contents);
    }
}
