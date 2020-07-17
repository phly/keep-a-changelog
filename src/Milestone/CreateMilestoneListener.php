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

class CreateMilestoneListener
{
    public function __invoke(CreateMilestoneEvent $event) : void
    {
        $title       = $event->title();
        $description = $event->description();
        $provider    = $event->provider();

        $event->output()->writeln(sprintf(
            '<info>Creating milestone %s (%s)</info>',
            $title,
            $description
        ));

        try {
            /** @var ProviderInterface|MilestoneAwareProviderInterface $provider */
            $milestone = $provider->createMilestone($title, $description);
        } catch (Throwable $e) {
            $event->errorCreatingMilestone($e);
            return;
        }

        $event->milestoneCreated($milestone);
    }
}
