<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Unreleased;

use function preg_match;

class ValidateDateToUseListener
{
    public function __invoke(PromoteEvent $event): void
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $event->releaseDate())) {
            return;
        }

        $event->didNotPromote();
    }
}
