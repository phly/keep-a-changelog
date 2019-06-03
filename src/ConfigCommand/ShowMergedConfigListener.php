<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\ArrayMergeRecursiveTrait;
use Phly\KeepAChangelog\Common\IniReadWriteTrait;
use Phly\KeepAChangelog\Config\LocateGlobalConfigTrait;

class ShowMergedConfigListener
{
    use ArrayMergeRecursiveTrait;
    use IniReadWriteTrait;
    use LocateGlobalConfigTrait;
    use MaskProviderTokensTrait;

    public function __invoke(ShowConfigEvent $event) : void
    {
        if (! $event->showMerged()) {
            return;
        }

        $globalConfigFile = sprintf('%s/keep-a-changelog.ini', $this->getConfigRoot());
        if (! is_readable($globalConfigFile)) {
            $event->configIsNotReadable($globalConfigFile, 'global');
            return;
        }

        $localConfigFile = sprintf('%s/.keep-a-changelog.ini', $this->localConfigRoot ?: getcwd());
        if (! is_readable($localConfigFile)) {
            $event->configIsNotReadable($localConfigFile, 'local');
            return;
        }

        $config = $this->arrayMergeRecursive(
            $this->readIniFile($globalConfigFile),
            $this->readIniFile($localConfigFile)
        );

        $event->displayMergedConfig(
            $this->arrayToIniString($this->maskProviderTokens($config))
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
    public $localConfigRoot;
}
