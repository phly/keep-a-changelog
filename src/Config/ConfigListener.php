<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use Symfony\Component\Console\Helper\QuestionHelper;

class ConfigListener
{
    /** @var bool */
    protected $requiresPackageName;

    /** @var bool */
    protected $requiresRemoteName;

    public function __construct(
        bool $requiresPackageName = false,
        bool $requiresRemoteName = false
    ) {
        $this->requiresPackageName = $requiresPackageName;
        $this->requiresRemoteName  = $requiresRemoteName;
    }

    public function __invoke(EventInterface $event): void
    {
        $input  = $event->input();
        $output = $event->output();

        $configDiscovery = $event->dispatcher()->dispatch(
            new ConfigDiscovery($input, $output)
        );

        $config = $configDiscovery->config();

        if (! $this->packageCheck($event, $config)) {
            $event->configurationIncomplete();
            return;
        }

        if (! $this->remoteCheck($event, $config)) {
            $event->configurationIncomplete();
            return;
        }

        $event->discoveredConfiguration($config);
    }

    private function packageCheck(EventInterface $event, Config $config): bool
    {
        if (! $this->requiresPackageName) {
            return true;
        }

        if ($config->package()) {
            return true;
        }

        $input  = $event->input();
        $output = $event->output();

        if (
            $event->dispatcher()
            ->dispatch(new PackageNameDiscovery($input, $output, $config))
            ->packageWasFound()
        ) {
            return true;
        }

        $output->writeln('<error>Unable to determine package name</error>');
        $output->writeln('You will need to do one of the following:');
        $output->writeln('- Add a "package" setting under the [defaults] section of .keep-a-changelog.ini');
        $output->writeln('- Pass the package name via the --package option');

        return false;
    }

    private function remoteCheck(EventInterface $event, Config $config): bool
    {
        if (! $this->requiresRemoteName) {
            return true;
        }

        if ($config->remote()) {
            return true;
        }

        $input  = $event->input();
        $output = $event->output();

        if (
            $event->dispatcher()
            ->dispatch(new RemoteNameDiscovery($input, $output, $config, new QuestionHelper()))
            ->remoteWasFound()
        ) {
            return true;
        }

        $output->writeln('<error>Unable to determine Git remote</error>');
        $output->writeln('You will need to do one of the following:');
        $output->writeln('- Add a "remote" setting under the [defaults] section of .keep-a-changelog.ini');
        $output->writeln('- Pass the remote name via the --remote option');

        return false;
    }
}
