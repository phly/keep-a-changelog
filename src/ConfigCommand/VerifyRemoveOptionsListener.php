<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

class VerifyRemoveOptionsListener
{
    public function __invoke(RemoveConfigEvent $event): void
    {
        if (! $event->removeLocal() && ! $event->removeGlobal()) {
            $event->missingOptions();
            return;
        }
    }
}
