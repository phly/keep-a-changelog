<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use function is_readable;

abstract class AbstractShowConfigListener
{
    abstract public function shouldShowConfig(ShowConfigEvent $event): bool;

    abstract public function getConfigFile(): string;

    abstract public function getConfigType(): string;

    abstract public function displayConfig(ShowConfigEvent $event, string $configFile): void;

    public function __invoke(ShowConfigEvent $event): void
    {
        if (! $this->shouldShowConfig($event)) {
            return;
        }

        $configFile = $this->getConfigFile();
        if (! is_readable($configFile)) {
            $event->configIsNotReadable($configFile, $this->getConfigType());
            return;
        }

        $this->displayConfig($event, $configFile);
    }
}
