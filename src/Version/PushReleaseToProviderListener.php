<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Throwable;

use function sprintf;

class PushReleaseToProviderListener
{
    public function __invoke(ReleaseEvent $event): void
    {
        $releaseName = $event->releaseName();
        $provider    = $event->provider();

        $event->output()->writeln(sprintf(
            '<info>Creating release "%s"</info>',
            $releaseName
        ));

        try {
            $release = $provider->createRelease(
                $releaseName,
                $event->tagName(),
                $event->changelog()
            );
        } catch (Throwable $e) {
            $event->errorCreatingRelease($e);
            return;
        }

        if (! $release) {
            $event->unexpectedProviderResult();
            return;
        }

        $event->releaseCreated($release);
    }
}
