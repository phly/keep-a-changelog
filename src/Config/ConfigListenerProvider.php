<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Psr\EventDispatcher\ListenerProviderInterface;

class ConfigListenerProvider implements ListenerProviderInterface
{
    private $listeners = [
        ConfigDiscovery::class => [
            RetrieveGlobalConfigListener::class,
            RetrieveLocalConfigListener::class,
            RetrieveInputOptionsListener::class,
        ],
        PackageNameDiscovery::class => [
            DiscoverPackageFromComposerListener::class,
            DiscoverPackageFromNpmPackageListener::class,
            DiscoverPackageFromGitRemoteListener::class,
        ],
        RemoteNameDiscovery::class => [
            DiscoverRemoteFromGitRemotesListener::class,
            PromptForGitRemoteListener::class,
        ],
    ];

    public function getListenersForEvent(object $event) : iterable
    {
        $type = gettype($event);
        if (! isset($this->listeners[$type])) {
            return [];
        }

        foreach ($this->listeners[$type] as $className) {
            yield new $className();
        }
    }
}
