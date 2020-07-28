<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Provider\ProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

use function sprintf;

/**
 * Provides methods for injecting common (but not global) input options.
 */
trait CommonConfigOptionsTrait
{
    private function injectPackageOption(Command $command): void
    {
        $command->addOption(
            'package',
            'p',
            InputOption::VALUE_REQUIRED,
            'Package to release; must be in valid for the provider you use.'
        );
    }

    private function injectRemoteOption(Command $command): void
    {
        $command->addOption(
            'remote',
            'r',
            InputOption::VALUE_REQUIRED,
            'Git remote to push tag to; defaults to first Git remote matching provider and package'
        );
    }

    private function injectProviderOptions(Command $command): void
    {
        $command->addOption(
            'provider',
            null,
            InputOption::VALUE_REQUIRED,
            'Named repository provider, based on configuration file definitions;'
            . ' "github" and "gitlab" are always available; default is "github"'
        );

        $command->addOption(
            'provider-class',
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'Name of a resolvable PHP class that implements %s for use as your'
                . ' repository provider; overrides the --provider option when used',
                ProviderInterface::class
            )
        );

        $command->addOption(
            'provider-url',
            null,
            InputOption::VALUE_REQUIRED,
            'Custom base URL for use with your selected repository provider;'
            . ' primarily applies to enterprise github or self-hosted gitlab'
        );

        $command->addOption(
            'provider-token',
            null,
            InputOption::VALUE_REQUIRED,
            'Personal access/OAuth2 token to use for authenticating with your provider;'
            . ' this value is REQUIRED in order to create releases.'
        );
    }
}
