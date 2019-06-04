<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use Github\Client as GitHubClient;
use Github\Exception\ExceptionInterface as GithubException;

use function explode;
use function filter_var;
use function preg_match;
use function rawurlencode;
use function sprintf;

use const FILTER_VALIDATE_URL;

class GitHub implements ProviderInterface
{
    private const DEFAULT_URL    = 'https://api.github.com';

    /**
     * Use for testing purposes only.
     *
     * @internal
     * @var ?GitHubClient
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
        return null !== $this->package
            && null !== $this->token;
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

        [$org, $repo] = explode('/', $this->package);
        $client       = $this->getClient();

        $this->verifyTag($client, $org, $repo, $tagName);

        $release = $client->api('repo')->releases()->create(
            $org,
            $repo,
            [
                'tag_name'   => $tagName,
                'name'       => $releaseName,
                'body'       => $changelog,
                'draft'      => false,
                'prerelease' => $this->isVersionPrelease($tagName),
            ]
        );

        return $release['html_url'] ?? null;
    }

    public function generateIssueLink(int $issueIdentifier) : string
    {
        if (! $this->package) {
            throw Exception\MissingPackageNameException::for($this, 'issue link generation');
        }

        $baseUrl = $this->url === self::DEFAULT_URL
            ? 'https://github.com'
            : $this->url;

        $url = sprintf('%s/%s/issues/%d', $baseUrl, $this->package, $issueIdentifier);
        return sprintf('[#%d](%s)', $issueIdentifier, $url);
    }

    public function generatePatchLink(int $patchIdentifier) : string
    {
        if (! $this->package) {
            throw Exception\MissingPackageNameException::for($this, 'patch link generation');
        }

        $baseUrl = $this->url === self::DEFAULT_URL
            ? 'https://github.com'
            : $this->url;

        $url = sprintf('%s/%s/pull/%d', $baseUrl, $this->package, $patchIdentifier);
        return sprintf('[#%d](%s)', $patchIdentifier, $url);
    }

    public function setPackageName(string $package) : void
    {
        if (! preg_match('#^[a-z0-9]+[a-z0-9_-]*/[a-z0-9]+[a-z0-9_-]*$#i', $package)) {
            throw Exception\InvalidPackageNameException::forPackage($package, $this);
        }
        $this->package = $package;
    }

    public function setToken(string $token) : void
    {
        $this->token = $token;
    }

    /**
     * Set the base URL to use for API calls to the provider.
     *
     * Generally, this should only be the scheme + authority.
     */
    public function setUrl(string $url) : void
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw Exception\InvalidUrlException::forUrl($url, $this);
        }
        $this->url = $url;
    }

    /**
     * @throws Exception\MissingTagException if unable to verify the tag exists
     * @throws Exception\MissingTagException if unable to fetch tag data
     * @throws Exception\MissingTagException if the tag on github is not signed
     */
    private function verifyTag(GitHubClient $client, string $org, string $repo, string $tagName) : void
    {
        try {
            $tagRef = $client
                ->api('gitData')
                ->references()
                ->show($org, $repo, 'tags/' . rawurlencode($tagName));
        } catch (GithubException $e) {
            throw Exception\MissingTagException::forPackageOnGithub(
                sprintf('%s/%s', $org, $repo),
                $tagName,
                $e
            );
        }

        try {
            $tagData = $client
                ->api('gitData')
                ->tags()
                ->show($org, $repo, $tagRef['object']['sha']);
        } catch (GithubException $e) {
            throw Exception\MissingTagException::forTagOnGithub(
                sprintf('%s/%s', $org, $repo),
                $tagName,
                $e
            );
        }

        if (! $tagData['verification']['verified']) {
            throw Exception\MissingTagException::forUnverifiedTagOnGithub(
                sprintf('%s/%s', $org, $repo),
                $tagName
            );
        }
    }

    private function getClient() : GitHubClient
    {
        if ($this->client instanceof GitHubClient) {
            return $this->client;
        }

        $client = self::DEFAULT_URL === $this->url
            ? new GitHubClient()
            : new GitHubClient(null, null, $this->url);
        $client->authenticate($this->token, GitHubClient::AUTH_HTTP_TOKEN);

        return $client;
    }

    private function isVersionPrelease(string $version) : bool
    {
        if (preg_match('/(alpha|a|beta|b|rc|dev)\d+$/i', $version)) {
            return true;
        }
        return false;
    }
}
