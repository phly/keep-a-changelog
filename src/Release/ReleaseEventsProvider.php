<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Psr\EventDispatcher\ListenerProviderInterface;

class ReleaseEventsProvider implements ListenerProviderInterface
{
    private $listeners = [
        ValidateRequirementsEvent::class => [
            VerifyTagExistsListener::class,
            MarshalConfigurationListener::class,
            ValidateTokenExistsListener::class,
        ],
    ];

    public function getListenersForEvent(object $event) : iterable
    {
        $type = gettype($event);
        if (! isset($this->listeners[$type])) {
            yield from [];
        }

        foreach ($this->listeners[$type] as $listeners) {
            yield new $class();
        }
    }
}
