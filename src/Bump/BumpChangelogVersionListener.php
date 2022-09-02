<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Bump;

class BumpChangelogVersionListener
{
    public function __invoke(BumpChangelogVersionEvent $event): void
    {
        $bumper  = new ChangelogBump($event->config()->changelogFile());
        $version = $event->version() ?: $this->lookupLatestVersionInChangelog($bumper, $event->bumpMethod());
        $bumper->updateChangelog($version);
        $event->bumpedChangelog($version);
    }

    private function lookupLatestVersionInChangelog(ChangelogBump $bumper, string $method): string
    {
        $latest = $bumper->findLatestVersion();
        return $bumper->$method($latest);
    }
}
