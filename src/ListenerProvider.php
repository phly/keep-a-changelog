<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    private $listeners = [
        Bump\BumpChangelogVersionEvent::class => [
            Config\ConfigListener::class,
            Common\IsChangelogReadableListener::class,
            Bump\BumpChangelogVersionListener::class,
        ],
        Release\ReleaseEvent::class => [
            Release\ConfigListener::class,
            Release\VerifyTagExistsListener::class,
            Release\VerifyProviderCanReleaseListener::class,
            Common\IsChangelogReadableListener::class,
            Release\ParseChangelogListener::class,
            Release\FormatChangelogListener::class,
            Release\PushTagToRemoteListener::class,
            Release\CreateReleaseNameListener::class,
            Release\PushReleaseToProviderListener::class,
        ],
    ];

    public function getListenersForEvent(object $event) : iterable
    {
        $type = gettype($event);
        if (! isset($this->listeners[$type])) {
            return [];
        }

        foreach ($this->listeners[$type] as $listener) {
            $listener = is_object($listener)
                ? $listener
                : new $listener();
            yield $listener;
        }
    }
}
