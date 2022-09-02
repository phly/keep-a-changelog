<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

class VerifyEditOptionsListener
{
    public function __invoke(EditConfigEvent $event): void
    {
        if ($event->editLocal() && $event->editGlobal()) {
            $event->tooManyOptions();
            return;
        }
    }
}
