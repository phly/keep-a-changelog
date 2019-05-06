<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use Gitlab\Client as GitLabClient;
use Phly\KeepAChangelog\Exception;

class GitLab implements
    IssueMarkupProvider,
    ProviderInterface
{
    public function getIssuePrefix() : string
    {
        return '#';
    }

    public function getPatchPrefix() : string
    {
        return '!';
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
        $client = GitLabClient::create('https://gitlab.com');
        $client->authenticate($token, GitLabClient::AUTH_HTTP_TOKEN);
        $release = $client->api('repositories')->createRelease($package, $tagName, $changelog);

        return $release['tag_name'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryUrlRegex() : string
    {
        return '(gitlab.com[:/](.*?)\.git)';
    }

    /**
     * @inheritDoc
     */
    public function generatePullRequestLink(string $package, int $pr) : string
    {
        if (! preg_match('#^[a-z0-9]+[a-z0-9_-]*(/[a-z0-9]+[a-z0-9_-]*)+$#i', $package)) {
            throw Exception\InvalidPackageNameException::forPackage($package);
        }

        return sprintf('https://gitlab.com/%s/merge_requests/%d', $package, $pr);
    }
}
