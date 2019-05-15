<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use function getcwd;
use function realpath;

class DiscoverPackageFromComposerListener extends AbstractDiscoverPackageFromFileListener
{
    protected function getFileName() : string
    {
        return realpath(getcwd()) . '/composer.json';
    }
}
