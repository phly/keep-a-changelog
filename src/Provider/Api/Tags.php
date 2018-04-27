<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider\Api;

use Gitlab\Api\Tags as GitLabTags;

class Tags extends GitLabTags
{
    /**
     * @param $projectId
     * @param $tagName
     * @param string $description
     * @return mixed
     */
    public function createRelease($projectId, $tagName, string $description)
    {
        return $this->post(
            $this->getProjectPath($projectId, "repository/tags/$tagName/release"),
            ['description' => $description]
        );
    }
}
