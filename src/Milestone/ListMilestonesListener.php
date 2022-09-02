<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Throwable;

class ListMilestonesListener
{
    public function __invoke(ListMilestonesEvent $event): void
    {
        /** @var ProviderInterface|MilestoneAwareProviderInterface $provider */
        $provider = $event->provider();

        $event->output()->writeln('<info>Fetching milestones...</info>');

        try {
            $milestones = $provider->listMilestones();
        } catch (Throwable $e) {
            $event->errorListingMilestones($e);
            return;
        }

        $event->milestonesRetrieved($milestones);
    }
}
