<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use function getcwd;
use function realpath;

class DiscoverPackageFromNpmPackageListener extends AbstractDiscoverPackageFromFileListener
{
    protected function getFileName(): string
    {
        $path = $this->packageDir ?: realpath(getcwd());
        return $path . '/package.json';
    }
}
