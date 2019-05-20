<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config\ConfigListener;
use Psr\EventDispatcher\ListenerProviderInterface;

class ReleaseListenerProvider implements ListenerProviderInterface
{
    private $listeners = [
        CreateReleaseEvent::class => [
            CreateReleaseNameListener::class,
            PushReleaseToProviderListener::class,
        ],
    ];

    public function __construct()
    {
        $this->listeners[ReleaseEvent::class] = [
            new ConfigListener(
                $requiresPackageName = true,
                $requiresRemoteName = true
            ),
            VerifyTagExistsListener::class,
            VerifyProviderCanReleaseListener::class,
            DiscoverChangelogFileListener::class,
            ParseChangelogListener::class,
            FormatChangelogListener::class,
            PushTagToRemoteListener::class,
            CreateReleaseListener::class,
        ];
    }

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
