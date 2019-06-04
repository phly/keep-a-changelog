<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use Gitlab\Client as GitLabClient;

use function filter_var;
use function parse_url;
use function sprintf;

use const FILTER_VALIDATE_URL;
use const PHP_URL_HOST;

class GitLab implements ProviderInterface
{
    private const DEFAULT_URL = 'https://gitlab.com';

    /**
     * Use for testing purposes only.
     *
     * @internal
     * @var ?GitLabClient
     */
    public $client;

    /** @var ?string */
    private $package;

    /** @var ?string */
    private $token;

    /** @var string */
    private $url = self::DEFAULT_URL;

    public function canCreateRelease() : bool
    {
        return null !== $this->token
            && null !== $this->package;
    }

    public function canGenerateLinks() : bool
    {
        return null !== $this->package;
    }

    public function createRelease(
        string $releaseName,
        string $tagName,
        string $changelog
    ) : ?string {
        if (! $this->package) {
            throw Exception\MissingPackageNameException::for($this, 'release creation');
        }

        if (! $this->token) {
            throw Exception\MissingTokenException::for($this);
        }

        $release = $this->getClient()->api('repositories')
            ->createRelease($this->package, $tagName, $changelog);

        return $release['tag_name'] ?? null;
    }

    public function generateIssueLink(int $issueIdentifier) : string
    {
        if (! $this->package) {
            throw Exception\MissingPackageNameException::for($this, 'issue link generation');
        }
        $url = sprintf('%s/%s/issues/%d', $this->url, $this->package, $issueIdentifier);
        return sprintf('[#%d](%s)', $issueIdentifier, $url);
    }

    public function generatePatchLink(int $patchIdentifier) : string
    {
        if (! $this->package) {
            throw Exception\MissingPackageNameException::for($this, 'patch link generation');
        }
        $url = sprintf('%s/%s/merge_requests/%d', $this->url, $this->package, $patchIdentifier);
        return sprintf('[!%d](%s)', $patchIdentifier, $url);
    }

    public function setPackageName(string $package) : void
    {
        if (! preg_match('#^[a-z0-9]+[a-z0-9_-]*(/[a-z0-9]+[a-z0-9_-]*)+$#i', $package)) {
            throw Exception\InvalidPackageNameException::forPackage($package, $this);
        }
        $this->package = $package;
    }

    public function setToken(string $token) : void
    {
        $this->token = $token;
    }

    public function setUrl(string $url) : void
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw Exception\InvalidUrlException::forUrl($url, $this);
        }
        $this->url = $url;
    }

    private function getClient() : GitLabClient
    {
        if ($this->client instanceof GitLabClient) {
            return $this->client;
        }

        $client = GitLabClient::create($this->url);
        $client->authenticate($this->token, GitLabClient::AUTH_HTTP_TOKEN);

        return $client;
    }
}
