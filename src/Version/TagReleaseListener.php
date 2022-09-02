<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use function file_put_contents;
use function sprintf;
use function sys_get_temp_dir;
use function system;
use function tempnam;
use function unlink;

class TagReleaseListener
{
    public function __invoke(TagReleaseEvent $event): void
    {
        $version = $event->version();

        $event->output()->writeln(sprintf('<info>Preparing to tag version %s</info>', $version));

        if (
            ! $this->tagWithChangelog(
                $event->tagName(),
                $event->package(),
                $version,
                $event->changelog()
            )
        ) {
            $event->tagOperationFailed();
            return;
        }

        $event->taggingComplete();
    }

    private function tagWithChangelog(string $tagName, string $package, string $version, string $changelog): bool
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($tempFile, sprintf("%s %s\n\n%s", $package, $version, $changelog));

        $command = sprintf('git tag -s -F %s %s', $tempFile, $tagName);
        system($command, $return);

        unlink($tempFile);

        return 0 === $return;
    }
}
