<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Config\LocateGlobalConfigTrait;

class CreateGlobalConfigListener extends AbstractCreateConfigListener
{
    use LocateGlobalConfigTrait;

    private const TEMPLATE = <<< 'EOT'
[defaults]
changelog_file = %s
provider = github
remote = origin

[providers]
github[class] = Phly\KeepAChangelog\Provider\GitHub
github[token] = token-should-be-provided-here
gitlab[class] = Phly\KeepAChangelog\Provider\GitLab
gitlab[token] = token-should-be-provided-here

EOT;

    public function configCreateRequested(CreateConfigEvent $event) : bool
    {
        return $event->createGlobal();
    }

    public function getConfigFileName() : string
    {
        return sprintf('%s/keep-a-changelog.ini', $this->getConfigRoot());
    }

    public function getConfigTemplate() : string
    {
        return self::TEMPLATE;
    }
}
