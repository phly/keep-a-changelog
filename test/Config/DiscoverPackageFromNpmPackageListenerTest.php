<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config\AbstractDiscoverPackageFromFileListener;
use Phly\KeepAChangelog\Config\DiscoverPackageFromNpmPackageListener;

class DiscoverPackageFromNpmPackageListenerTest extends AbstractDiscoverPackageFromFileListenerTest
{
    public function createListener(): AbstractDiscoverPackageFromFileListener
    {
        return new DiscoverPackageFromNpmPackageListener();
    }
}
