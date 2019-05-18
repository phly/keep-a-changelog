<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

class DiscoverPackageFromGitRemoteListener
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

    public function __invoke(PackageNameDiscovery $event) : void
    {
        if ($event->packageWasFound()) {
            // Already discovered
            return;
        }

        $config   = $event->config();
        $provider = $config->provider();
        $domain   = $this->getDomainFromProviderUrl($provider->url());

        if (! $domain) {
            // No way to match
            return;
        }

        $regex = sprintf(
            '#[/@.]%s(:\d+:|:|/)(?P<package>.*?)\.git$#',
            preg_quote($domain)
        );

        foreach ($this->getRemotes() as $remote) {
            if (preg_match($regex, $remote, $matches)) {
                $event->foundPackage($matches['package']);
                return;
            }
        }
    }

    private function getRemotes() : iterable
    {
        $exec    = $this->exec;
        $remotes = [];
        $return  = 0;

        $exec('git remote', $remotes, $return);
        if (0 !== $return) {
            yield from [];
            return;
        }

        foreach ($remotes as $remote) {
            $output = [];
            $exec(sprintf('git remote get-url %s', escapeshellarg($remote)), $output, $return);

            if (0 !== $return) {
                continue;
            }

            yield array_pop($output);
        }
    }

    private function getDomainFromProviderUrl(?string $url) : ?string
    {
        if (! $url) {
            return $url;
        }

        return parse_url($url, PHP_URL_HOST);
    }
}
