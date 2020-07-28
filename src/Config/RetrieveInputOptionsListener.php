<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use Symfony\Component\Console\Input\InputInterface;

use function class_exists;

/**
 * Look for input options related to configuration and providesrs.
 *
 * Looks for the following options
 */
class RetrieveInputOptionsListener
{
    public function __invoke(ConfigDiscovery $event): void
    {
        $input  = $event->input();
        $config = $event->config();

        $this->processProviderOptions($input, $config);

        $changelogFile = $input->hasOption('changelog') ? $input->getOption('changelog') : null;
        if ($changelogFile) {
            $config->setChangelogFile($changelogFile);
        }

        $package = $input->hasOption('package') ? $input->getOption('package') : null;
        if ($package) {
            $config->setPackage($package);
        }

        $remote = $input->hasOption('remote') ? $input->getOption('remote') : null;
        if ($remote) {
            $config->setRemote($remote);
        }
    }

    private function processProviderOptions(InputInterface $input, Config $config): void
    {
        $provider = $this->getProvider($input, $config);

        $token = $input->hasOption('provider-token') ? $input->getOption('provider-token') : null;
        if ($token) {
            $provider->setToken($token);
        }

        $url = $input->hasOption('provider-url') ? $input->getOption('provider-url') : null;
        if ($url) {
            $provider->setUrl($url);
        }
    }

    private function getProvider(InputInterface $input, Config $config): ProviderSpec
    {
        if ($input->hasOption('provider-class') && $input->getOption('provider-class')) {
            return $this->createProviderFromOption($input->getOption('provider-class'), $config);
        }

        if ($input->hasOption('provider') && $input->getOption('provider')) {
            return $this->fetchProviderByName($input->getOption('provider'), $config);
        }

        return $config->provider();
    }

    private function createProviderFromOption(string $class, Config $config): ProviderSpec
    {
        if (! class_exists($class)) {
            throw Exception\InvalidProviderException::forMissingClass($class, '--provider-class input option');
        }

        $spec = new ProviderSpec('--provider-class');
        $spec->setClassName($class);

        $config->providers()->add($spec);
        $config->setProviderName('--provider-class');

        return $spec;
    }

    private function fetchProviderByName(string $name, Config $config): ProviderSpec
    {
        $providers = $config->providers();
        if (! $providers->has($name)) {
            throw Exception\InvalidProviderException::forMissingProvider(
                $name,
                $providers->listKnownTypes(),
                '--provider input option'
            );
        }

        $config->setProviderName($name);
        return $config->provider();
    }
}
