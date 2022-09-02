<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use function sprintf;

class VerifyTagExistsListener
{
    /**
     * exec()
     *
     * This property exists for testing purposes only. The signature is:
     *
     * <code>
     * function(string $command[, array &$output[, int &$return]])
     * </code>
     *
     * @var callable
     */
    public $exec = 'exec';

    public function __invoke(ReleaseEvent $event): void
    {
        $command = sprintf('git show %s', $event->tagName());
        $exec    = $this->exec;
        $exec($command, $output, $return);
        if (0 !== $return) {
            $event->couldNotFindTag();
        }
    }
}
