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

use function sprintf;

class CloseMilestoneListener
{
    public function __invoke(CloseMilestoneEvent $event): void
    {
        $id       = $event->id();
        $provider = $event->provider();

        $event->output()->writeln(sprintf(
            '<info>Closing milestone %d</info>',
            $id
        ));

        try {
            $status = $provider->closeMilestone($id);
        } catch (Throwable $e) {
            $event->errorClosingMilestone($e);
            return;
        }

        $event->milestoneClosed();
    }
}
