<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use function getcwd;
use function implode;
use function sprintf;

class CreateLocalConfigListener extends AbstractCreateConfigListener
{
    public function configCreateRequested(CreateConfigEvent $event): bool
    {
        return $event->createLocal();
    }

    public function getConfigFileName(): string
    {
        return sprintf('%s/.keep-a-changelog.ini', $this->configRoot ?: getcwd());
    }

    public function getConfigTemplate(): string
    {
        // Done this way due to issues with PHAR creation
        return implode("\n", [
            '[defaults]',
            'changelog_file = %s',
            'provider = github',
            'remote = origin',
            '',
            '[providers]',
            'github[class] = Phly\KeepAChangelog\Provider\GitHub',
            'gitlab[class] = Phly\KeepAChangelog\Provider\GitLab',
            '',
        ]);
    }

    /**
     * Set a specific directory in which to look for the local config file.
     *
     * For testing purposes only.
     *
     * @internal
     *
     * @var null|string
     */
    public $configRoot;
}
