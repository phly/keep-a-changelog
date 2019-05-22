<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Matomo\Ini;
use Phly\KeepAChangelog\Config\LocateGlobalConfigTrait;

class ShowGlobalConfigListener
{
    use LocateGlobalConfigTrait;
    use MaskProviderTokensTrait;

    public function __invoke(ShowConfigEvent $event) : void
    {
        if (! $event->showGlobal() || $event->showMerged()) {
            return;
        }

        $configFile = sprintf('%s/keep-a-changelog.ini', $this->getConfigRoot());
        if (! is_readable($configFile)) {
            $event->configIsNotReadable($configFile, 'global');
            return;
        }

        $event->displayConfig(
            $this->filterConfiguration($configFile),
            'global',
            $configFile
        );
    }

    private function filterConfiguration(string $configFile) : string
    {
        $config = (new Ini\IniReader())->readFile($configFile);
        return (new Ini\IniWriter())->writeToString($this->maskProviderTokens($config));
    }
}
