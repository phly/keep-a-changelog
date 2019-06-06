<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\EditSpawnerTrait;

use function file_exists;

abstract class AbstractEditConfigListener
{
    use EditSpawnerTrait;

    abstract public function configEditRequested(EditConfigEvent $event) : bool;

    abstract public function getConfigFile() : string;

    public function __invoke(EditConfigEvent $event) : void
    {
        if (! $this->configEditRequested($event)) {
            return;
        }

        $configFile = $this->getConfigFile();

        if (! file_exists($configFile)) {
            $event->configFileNotFound($configFile);
            return;
        }

        $status = $this->getEditor()->spawnEditor(
            $event->output(),
            $event->editor(),
            $configFile
        );

        if (0 !== $status) {
            $event->editFailed($configFile);
            return;
        }

        $event->editComplete($configFile);
    }
}
