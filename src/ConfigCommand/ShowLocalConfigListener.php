<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

class ShowLocalConfigListener
{
    public function __invoke(ShowConfigEvent $event) : void
    {
        if (! $event->showLocal() || $event->showMerged()) {
            return;
        }

        $configFile = sprintf('%s/.keep-a-changelog.ini', getcwd());
        if (! is_readable($configFile)) {
            $event->configIsNotReadable($configFile, 'local');
            return;
        }

        $event->displayConfig(
            file_get_contents($configFile),
            'global',
            $configFile
        );
    }
}
