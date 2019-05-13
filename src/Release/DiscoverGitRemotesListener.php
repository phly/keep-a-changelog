<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use function exec;

class DiscoverGitRemotesListener
{
    /**
     * Callable to use to execute "git remote -v" command.
     *
     * This feature exists solely for testing. The callable should have the
     * signature:
     *
     * <code>
     * function (string $command[, array &$output[, int &$exitStatus]]) : void
     * </code>
     *
     * @internal
     * @var callable
     */
    public $exec = 'exec';

    public function __invoke(PushTagEvent $event) : void
    {
        if ($event->remote()) {
            return;
        }

        $exec       = $this->exec;
        $command    = 'git remote -v';
        $output     = [];
        $exitStatus = 0;

        $exec($command, $output, $exitStatus);
        if ($exitStatus !== 0) {
            $event->gitRemoteResolutionFailed();
            return;
        }

        $event->setRemotes($output);
    }
}
