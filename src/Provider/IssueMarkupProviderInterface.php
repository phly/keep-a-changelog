<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

interface IssueMarkupProviderInterface
{
    /**
     * Retrieve the prefix to use when generating markup for an issue.
     *
     * GitHub uses `#` to reference either issues or pull requests.
     * GitLab uses `#` for issues, and `!` for merge requests.
     */
    public function getIssuePrefix() : string;

    /**
     * Retrieve the prefix to use when generating markup for a patch.
     *
     * GitHub uses `#` to reference either issues or pull requests.
     * GitLab uses `#` for issues, and `!` for merge requests.
     */
    public function getPatchPrefix() : string;
}
