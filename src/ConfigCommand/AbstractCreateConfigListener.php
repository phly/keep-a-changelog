<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use function file_exists;
use function file_put_contents;
use function sprintf;

abstract class AbstractCreateConfigListener
{
    /**
     * Return whether or not a config creation should occur.
     */
    abstract public function configCreateRequested(CreateConfigEvent $event): bool;

    /**
     * Return the path to the config file to create.
     */
    abstract public function getConfigFileName(): string;

    /**
     * Return the template for the config file contents.
     */
    abstract public function getConfigTemplate(): string;

    public function __invoke(CreateConfigEvent $event): void
    {
        if (! $this->configCreateRequested($event)) {
            return;
        }

        $configFile = $this->getConfigFileName();

        if (file_exists($configFile)) {
            $event->fileExists($configFile);
            return;
        }

        $success = file_put_contents($configFile, sprintf(
            $this->getConfigTemplate(),
            $event->customChangelog() ?: 'CHANGELOG.md'
        ));

        if (false === $success) {
            $event->creationFailed($configFile);
            return;
        }

        $event->createdConfigFile($configFile);
    }
}
