<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Throwable;

class ListMilestonesListener
{
    public function __invoke(ListMilestonesEvent $event) : void
    {
        $provider = $event->provider();

        $event->output()->writeln('<info>Fetching milestones...</info>');

        try {
            /** @var ProviderInterface|MilestoneAwareProviderInterface $provider */
            $milestones = $provider->listMilestones();
        } catch (Throwable $e) {
            $event->errorListingMilestones($e);
            return;
        }

        $event->milestonesRetrieved($milestones);
    }
}
