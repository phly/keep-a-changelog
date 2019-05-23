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
    use LocateGlobalConfigTrait;
    use MaskProviderTokensTrait;

    public function __invoke(ShowConfigEvent $event) : void
    {
        if (! $event->showMerged()) {
            return;
        }

        $configFile = sprintf('%s/keep-a-changelog.ini', $this->getConfigRoot());
        if (! is_readable($configFile)) {
            $event->configIsNotReadable($configFile, 'global');
            return;
        }

        $config = (new Ini\IniReader())->readFile($configFile);

        $configFile = sprintf('%s/.keep-a-changelog.ini', getcwd());
        if (! is_readable($configFile)) {
            $event->configIsNotReadable($configFile, 'global');
            return;
        }

        $config = $this->arrayMergeRecursive(
            $config,
            $this->readIniFile($configFile)
        );

        $event->displayMergedConfig(
            $this->arrayToIniString($this->maskProviderTokens($config))
        );
    }
}
