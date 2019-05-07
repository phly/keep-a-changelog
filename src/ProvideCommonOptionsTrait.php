<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait ProvideCommonOptionsTrait
{
    private function injectConfigBasedOptions()
    {
        $this->addOption(
            'provider',
            null,
            InputOption::VALUE_OPTIONAL,
            'Repository provider. Options: github or gitlab; defaults to github'
        );
        $this->addOption(
            'provider-domain',
            null,
            InputOption::VALUE_OPTIONAL,
            'Custom domain for use with repository provider; primarily applies to self-hosted gitlab'
        );
        $this->addOption(
            'global',
            'g',
            InputOption::VALUE_NONE,
            'Use the global config file'
        );
    }
}
