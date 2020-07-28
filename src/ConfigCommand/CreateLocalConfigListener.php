<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use function getcwd;
use function sprintf;

class CreateLocalConfigListener extends AbstractCreateConfigListener
{
    private const TEMPLATE = <<<'EOT'
[defaults]
changelog_file = %s
provider = github
remote = origin

[providers]
github[class] = Phly\KeepAChangelog\Provider\GitHub
gitlab[class] = Phly\KeepAChangelog\Provider\GitLab

EOT;

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
        return self::TEMPLATE;
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
