<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config\AbstractDiscoverPackageFromFileListener;
use Phly\KeepAChangelog\Config\DiscoverPackageFromComposerListener;

class DiscoverPackageFromComposerListenerTest extends AbstractDiscoverPackageFromFileListenerTest
{
    public function createListener(): AbstractDiscoverPackageFromFileListener
    {
        return new DiscoverPackageFromComposerListener();
    }
}
