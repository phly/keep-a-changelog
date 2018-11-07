<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use Github\Client as GitHubClient;

class GitHub implements ProviderInterface
{
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
        return sprintf('https://github.com/%s/pull/%d', $package, $pr);
    }
}
