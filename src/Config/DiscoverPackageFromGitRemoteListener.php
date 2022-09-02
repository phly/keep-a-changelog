<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use function array_pop;
use function escapeshellarg;
use function parse_url;
use function preg_match;
use function preg_quote;
use function sprintf;

use const PHP_URL_HOST;

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
     *
     * @var callable
     */
    public $exec = 'exec';

    public function __invoke(PackageNameDiscovery $event): void
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

    private function getRemotes(): iterable
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

    private function getDomainFromProviderUrl(?string $url): ?string
    {
        if (! $url) {
            return $url;
        }

        return parse_url($url, PHP_URL_HOST);
    }
}
