<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\IniReadWriteTrait;
use Phly\KeepAChangelog\Config\LocateGlobalConfigTrait;

use function sprintf;

class ShowGlobalConfigListener extends AbstractShowConfigListener
{
    use IniReadWriteTrait;
    use LocateGlobalConfigTrait;
    use MaskProviderTokensTrait;

    public function shouldShowConfig(ShowConfigEvent $event): bool
    {
        return $event->showGlobal() && ! $event->showMerged();
    }

    public function getConfigFile(): string
    {
        return sprintf('%s/keep-a-changelog.ini', $this->getConfigRoot());
    }

    public function getConfigType(): string
    {
        return 'global';
    }

    public function displayConfig(ShowConfigEvent $event, string $configFile): void
    {
        $event->displayConfig(
            $this->filterConfiguration($configFile),
            'global',
            $configFile
        );
    }

    private function filterConfiguration(string $configFile): string
    {
        $config = $this->readIniFile($configFile);
        return $this->arrayToIniString($this->maskProviderTokens($config));
    }
}
