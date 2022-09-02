<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config\ConfigListener as BaseListener;

class ConfigListener extends BaseListener
{
    public function __invoke(EventInterface $event): void
    {
        if (
            $event->input()->getOption('pr')
            || $event->input()->getOption('issue')
        ) {
            $this->requiresPackageName = true;
        }
        parent::__invoke($event);
    }
}
