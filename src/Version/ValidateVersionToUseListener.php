<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ValidateVersionListener;
use Phly\KeepAChangelog\Common\VersionAwareEventInterface;

class ValidateVersionToUseListener extends ValidateVersionListener
{
    public function __invoke(VersionAwareEventInterface $event): void
    {
        if (! $event->version()) {
            // null is a valid version for this workflow; equates to "most recent"
            return;
        }

        parent::__invoke($event);
    }
}
