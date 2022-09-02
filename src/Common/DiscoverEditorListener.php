<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function getenv;
use function strpos;

class DiscoverEditorListener
{
    public function __invoke(EditorAwareEventInterface $event): void
    {
        if ($event->editor()) {
            // Passed as an argument; nothing to do
            return;
        }

        $editor = getenv('EDITOR');

        if ($editor) {
            $event->discoverEditor($editor);
            return;
        }

        $editor = isset($_SERVER['OS']) && false !== strpos($_SERVER['OS'], 'indows')
            ? 'notepad'
            : 'vi';
        $event->discoverEditor($editor);
    }
}
