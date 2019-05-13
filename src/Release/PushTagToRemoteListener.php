<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

class PushTagToRemoteListener
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
        $tagName = $event->tagName();
        $remote  = $event->remote();

        $event->output()->writeln(sprintf(
            '<info>Pushing tag %s to %s</info>',
            $tagName,
            $remote
        ));

        $command = sprintf('git push %s %s', $remote, $tagName);
        $exec    = $this->exec;
        $output  = [];
        $return  = 0;

        $exec($command, $output, $return);

        0 === $return
            ? $event->pushSucceeded()
            : $event->pushFailed();
    }
}
