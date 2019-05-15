<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use function is_readable;

abstract class AbstractDiscoverPackageFromFileListener
{
    abstract protected function getFileName() : string;

    public function __invoke(PackageNameDiscovery $event) : void
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
        if (! $package->name) {
            return;
        }

        $event->foundPackage($package->name);
    }
}
