<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

class DiscoverRemoteFromGitRemotesListener
{
    /**
     * Override internal exec command.
     *
     * For testing purposes only. When overriding, the callable should
     * have the following signature:
     *
     * <code>
     * function (string $command[, array &$output[, int &$exitStatus]]) : void
     * </code>
     *
     * @internal
     * @var callable
     */
    public $exec = 'exec';

    public function __invoke(RemoteNameDiscovery $event) : void
    {
        if ($event->remoteWasFound()) {
            // Already found
            return;
        }

        $config   = $event->config();
        $provider = $config->provider();
        $domain   = $this->getDomainFromProviderUrl($provider->url());
        if (! $domain) {
            // No way to match
            return;
        }

        $package = $config->package();
        if (! $package) {
            // No way to match
            return;
        }

        $remotes = $this->getRemotes($domain, $package);
        if (0 === count($remotes)) {
            $event->reportNoMatchingGitRemoteFound($domain, $package);
            return;
        }

        if (1 === count($remotes)) {
            $event->foundRemote(array_pop($remotes));
            return;
        }

        $event->setRemotes($remotes);
    }

    /**
     * @return string[]
     */
    private function getRemotes(string $domain, string $package) : array
    {
        $exec   = $this->exec;
        $output = [];
        $return = 0;

        $exec('git remote -v', $output, $return);
        if (0 !== $return) {
            return [];
        }

        $domainRegex = '#[/@.]' . preg_quote($domain) . '(:\d+:|:|/)#i';
        $discovered  = [];

        foreach ($output as $line) {
            if (! preg_match(
                '/^(?P<name>.*?)\s+(?P<url>.*?)\s+\((?P<type>.*?)\)$/',
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

        return $discovered;
    }

    private function getDomainFromProviderUrl(?string $url) : ?string
    {
        if (! $url) {
            return $url;
        }

        return parse_url($url, PHP_URL_HOST);
    }
}
