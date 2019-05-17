<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

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

    public function __invoke(ReleaseEvent $event) : void
    {
        $command = sprintf('git show %s', $event->tagName());
        $exec    = $this->exec;
        $exec($command, $output, $return);
        if (0 !== $return) {
            $event->couldNotFindTag();
        }
    }
}
