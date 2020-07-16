<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use function count;

class CheckTreeForChangesListener
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

    public function __invoke(TagReleaseEvent $event) : void
    {
        if ($event->input()->getOption('force')) {
            return;
        }

        $command = 'git status -s';
        $output  = [];
        $status  = 0;
        $exec    = $this->exec;

        $exec($command, $output, $status);

        if ($status !== 0 || count($output) > 0) {
            $event->taggingFailed();
            $event->output()->write('<error>You have changes present in your tree that are not checked in.</error>');
            $event->output()->write('Either check them in, or use the --force flag.');
        }
    }
}
