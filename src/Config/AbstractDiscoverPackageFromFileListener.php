<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use function file_get_contents;
use function is_readable;
use function json_decode;

abstract class AbstractDiscoverPackageFromFileListener
{
    /**
     * Set the directory in which the package resides.
     *
     * For testing purposes only. The path set here will be used to locate the
     * package file.
     *
     * @internal
     *
     * @var null|string
     */
    public $packageDir;

    abstract protected function getFileName(): string;

    public function __invoke(PackageNameDiscovery $event): void
    {
        if ($event->packageWasFound()) {
            // Already discovered
            return;
        }

        $packageFile = $this->getFileName();
        if (! is_readable($packageFile)) {
            // No package file present nothing to do.
            return;
        }

        $package = json_decode(file_get_contents($packageFile));
        if (! isset($package->name)) {
            return;
        }

        $event->foundPackage($package->name);
    }
}
