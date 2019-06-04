<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use function file_get_contents;
use function getcwd;
use function sprintf;

class ShowLocalConfigListener extends AbstractShowConfigListener
{
    public function shouldShowConfig(ShowConfigEvent $event) : bool
    {
        return $event->showLocal() && ! $event->showMerged();
    }

    public function getConfigFile() : string
    {
        return sprintf('%s/.keep-a-changelog.ini', $this->configRoot ?: getcwd());
    }

    public function getConfigType() : string
    {
        return 'local';
    }

    public function displayConfig(ShowConfigEvent $event, string $configFile) : void
    {
        $event->displayConfig(
            file_get_contents($configFile),
            'local',
            $configFile
        );
    }

    /**
     * Set a specific directory in which to look for the local config file.
     *
     * For testing purposes only.
     *
     * @internal
     * @var null|string
     */
    public $configRoot;
}
