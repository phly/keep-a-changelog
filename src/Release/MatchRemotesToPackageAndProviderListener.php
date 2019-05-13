<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Provider;

class MatchRemotesToPackageAndProviderListener
{
    public function __invoke(PushTagEvent $event) : void
    {
        if ($event->remote()) {
            return;
        }

        $config   = $event->config();
        $provider = $config->provider();
        $package  = $event->input()->getArgument('package');

        $providerDomain = $this->getProviderDomain($provider);
        if (! $providerDomain) {
            $event->invalidProviderDetected(gettype($provider));
            return;
        }

        $domainRegex = '#[/@.]' . preg_quote($providerDomain) . '(:\d+:|:|/)#i';
        $discovered  = [];

        foreach ($event->remotes() as $line) {
            if (! preg_match(
                '/^(?P<name>\S+)\s+(?P<url>\S+)\s+\((?P<type>[^)]+)\)$/',
                $line,
                $matches
            )) {
                continue;
            }

            if (strtolower($matches['type']) !== 'push') {
                continue;
            }

            if (! preg_match($domainRegex, $matches['url'])) {
                continue;
            }

            if (false === strstr($matches['url'], $package)) {
                continue;
            }

            // FOUND!
            $discovered[] = $matches['name'];
        }

        if (0 === count($discovered)) {
            $event->reportNoMatchingGitRemoteFound($providerDomain, $package);
            return;
        }

        if (1 === count($discovered)) {
            $event->setRemote(array_pop($discovered));
            return;
        }

        $event->setRemotes($discovered);
    }

    private function getProviderDomain(Provider\ProviderInterface $provider) : ?string
    {
        if (! $provider instanceof Provider\ProviderNameProviderInterface) {
            return null;
        }

        return $provider->getDomainName();
    }
}
