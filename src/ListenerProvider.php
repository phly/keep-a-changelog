<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Psr\EventDispatcher\ListenerProviderInterface;

use function get_class;
use function is_object;

class ListenerProvider implements ListenerProviderInterface
{
    private $listeners = [
        Bump\BumpChangelogVersionEvent::class      => [
            Config\ConfigListener::class,
            Common\IsChangelogReadableListener::class,
            Bump\BumpChangelogVersionListener::class,
        ],
        Changelog\CreateNewChangelogEvent::class   => [
            Config\ConfigListener::class,
            Common\ValidateVersionListener::class,
            Changelog\CreateNewChangelogListener::class,
        ],
        Changelog\ReadyLatestChangelogEvent::class => [
            Config\ConfigListener::class,
            Common\IsChangelogReadableListener::class,
            Common\DiscoverChangelogEntryListener::class,
            Changelog\SetDateForChangelogReleaseListener::class,
        ],
        ConfigCommand\CreateConfigEvent::class     => [
            ConfigCommand\CreateGlobalConfigListener::class,
            ConfigCommand\CreateLocalConfigListener::class,
        ],
        ConfigCommand\EditConfigEvent::class       => [
            ConfigCommand\VerifyEditOptionsListener::class,
            Common\DiscoverEditorListener::class,
            ConfigCommand\EditGlobalConfigListener::class,
            ConfigCommand\EditLocalConfigListener::class,
        ],
        ConfigCommand\RemoveConfigEvent::class     => [
            ConfigCommand\VerifyRemoveOptionsListener::class,
            ConfigCommand\RemoveLocalConfigListener::class,
            ConfigCommand\RemoveGlobalConfigListener::class,
        ],
        ConfigCommand\ShowConfigEvent::class       => [
            ConfigCommand\ShowGlobalConfigListener::class,
            ConfigCommand\ShowLocalConfigListener::class,
            ConfigCommand\ShowMergedConfigListener::class,
        ],
        Config\ConfigDiscovery::class              => [
            Config\RetrieveGlobalConfigListener::class,
            Config\RetrieveLocalConfigListener::class,
            Config\RetrieveInputOptionsListener::class,
        ],
        Config\PackageNameDiscovery::class         => [
            Config\DiscoverPackageFromComposerListener::class,
            Config\DiscoverPackageFromNpmPackageListener::class,
            Config\DiscoverPackageFromGitRemoteListener::class,
        ],
        Config\RemoteNameDiscovery::class          => [
            Config\DiscoverRemoteFromGitRemotesListener::class,
            Config\PromptForGitRemoteListener::class,
        ],
        Entry\AddChangelogEntryEvent::class        => [
            Entry\ConfigListener::class,
            Entry\IsEntryArgumentEmptyListener::class,
            Common\IsChangelogReadableListener::class,
            Common\DiscoverChangelogEntryListener::class,
            Entry\NotifyPreparingEntryListener::class,
            Entry\PrependIssueLinkListener::class,
            Entry\PrependPatchLinkListener::class,
            Entry\AddChangelogEntryListener::class,
        ],
        Release\ReleaseEvent::class                => [
            Release\ConfigListener::class,
            Release\VerifyTagExistsListener::class,
            Release\VerifyProviderCanReleaseListener::class,
            Common\IsChangelogReadableListener::class,
            Common\ParseChangelogListener::class,
            Common\FormatChangelogListener::class,
            Release\PushTagToRemoteListener::class,
            Release\CreateReleaseNameListener::class,
            Release\PushReleaseToProviderListener::class,
        ],
        Tag\TagReleaseEvent::class                 => [
            Tag\ConfigListener::class,
            Common\ValidateVersionListener::class,
            Common\IsChangelogReadableListener::class,
            Common\ParseChangelogListener::class,
            Common\FormatChangelogListener::class,
            Tag\TagReleaseListener::class,
        ],
        Version\EditChangelogVersionEvent::class   => [
            Config\ConfigListener::class,
            Common\IsChangelogReadableListener::class,
            Version\ValidateVersionToUseListener::class,
            Common\DiscoverChangelogEntryListener::class,
            Common\DiscoverEditorListener::class,
            Version\EditChangelogVersionListener::class,
        ],
        Version\ListVersionsEvent::class           => [
            Config\ConfigListener::class,
            Common\IsChangelogReadableListener::class,
            Version\ListVersionsListener::class,
        ],
        Version\RemoveChangelogVersionEvent::class => [
            Config\ConfigListener::class,
            Common\ValidateVersionListener::class,
            Common\IsChangelogReadableListener::class,
            Common\DiscoverChangelogEntryListener::class,
            Version\PromptForRemovalConfirmationListener::class,
            Version\RemoveChangelogVersionListener::class,
        ],
        Version\ShowVersionEvent::class            => [
            Config\ConfigListener::class,
            Common\IsChangelogReadableListener::class,
            Version\ValidateVersionToUseListener::class,
            Common\DiscoverChangelogEntryListener::class,
            Version\ShowVersionListener::class,
        ],
    ];

    public function getListenersForEvent(object $event) : iterable
    {
        $type = get_class($event);
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
