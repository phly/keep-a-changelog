<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use Gitlab\Client as GitLabClient;

class GitLab implements ProviderInterface
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
    ): ?string {
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
    public function generatePullRequestLink(string $package, int $pr): string
    {
        return sprintf('https://gitlab.com/%s/merge_requests/%d', $package, $pr);
    }
}
