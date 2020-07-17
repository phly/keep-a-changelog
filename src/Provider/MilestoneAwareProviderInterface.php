<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

interface MilestoneAwareProviderInterface
{
    /**
     * @return Milestone[]
     */
    public function listMilestones() : iterable;

    public function createMilestone(string $title, string $description = '') : Milestone;

    public function closeMilestone(int $id) : bool;
}
