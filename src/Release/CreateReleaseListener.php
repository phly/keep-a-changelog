<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

class CreateReleaseListener
{
    public function __invoke(ReleaseEvent $event) : void
    {
        $config = $event->config();

        $release = $event->dispatcher()->dispatch(new PrepareChangelogEvent(
            $event->input(),
            $event->output(),
            $event->provider(),
            $event->version(),
            $event->changelog(),
            $config->package()
        ));

        if (! $release->wasCreated()) {
            $event->releaseFailed();
        }
    }
}
