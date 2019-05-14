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
use Phly\KeepAChangelog\Exception;

class GitHub implements
    IssueMarkupProviderInterface,
    ProviderInterface,
    ProviderNameProviderInterface
{
    /** @var string */
    private $domain = 'github.com';

    public function getIssuePrefix() : string
    {
        return '#';
    }

    public function getPatchPrefix() : string
    {
        return '#';
    }

    /**
     * @inheritDoc
     */
    public function createRelease(
        string $package,
        string $releaseName,
        string $tagName,
        string $changelog,
        string $token
    ) : ?string {
        [$org, $repo] = explode('/', $package);
        $client = new GitHubClient();
        $client->authenticate($token, GitHubClient::AUTH_HTTP_TOKEN);

        $this->verifyTag($client, $org, $repo, $tagName);

        $release = $client->api('repo')->releases()->create(
            $org,
            $repo,
            [
                'tag_name'   => $tagName,
                'name'       => $releaseName,
                'body'       => $changelog,
                'draft'      => false,
                'prerelease' => false,
            ]
        );

        return $release['html_url'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryUrlRegex() : string
    {
        return '(github.com[:/](.*?)\.git)';
    }

    /**
     * @inheritDoc
     */
    public function generatePullRequestLink(string $package, int $pr) : string
    {
        if (! preg_match('#^[a-z0-9]+[a-z0-9_-]*/[a-z0-9]+[a-z0-9_-]*$#i', $package)) {
            throw Exception\InvalidPackageNameException::forPackage($package);
        }

        return sprintf('https://github.com/%s/pull/%d', $package, $pr);
    }

    public function getName() : string
    {
        return 'GitHub';
    }

    public function getDomainName() : string
    {
        return $this->domain;
    }

    public function withDomainName(string $domain) : ProviderNameProviderInterface
    {
        $new = clone $this;
        $new->domain = $domain;
        return $new;
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
}
