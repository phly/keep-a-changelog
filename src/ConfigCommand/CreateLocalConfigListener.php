<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

class CreateLocalConfigListener
{
    private const TEMPLATE = <<< 'EOT'
[defaults]
changelog_file = %s
provider = github
remote = origin

[providers]
github[class] = Phly\KeepAChangelog\Provider\GitHub
gitlab[class] = Phly\KeepAChangelog\Provider\GitLab

EOT;
    public function __invoke(CreateConfigEvent $event) : void
    {
        if (! $event->createLocal()) {
            return;
        }

        $configFile = sprintf('%s/.keep-a-changelog.ini', getcwd());

        if (file_exists($configFile)) {
            $event->fileExists($configFile);
            return;
        }

        $success = file_put_contents($configFile, sprintf(
            self::TEMPLATE,
            $event->customChangelog() ?: 'CHANGELOG.md'
        ));

        if (false === $success) {
            $event->creationFailed($configFile);
            return;
        }

        $event->createdConfigFile($config);
    }
}
