<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

class CheckForRemoteOptionListener
{
    public function __invoke(PushTagEvent $event) : void
    {
        if ($event->remote()) {
            return;
        }

        $remote = $event->input()->getOption('remote');

        if ($remote) {
            $event->setRemote($remote);
        }
    }
}
