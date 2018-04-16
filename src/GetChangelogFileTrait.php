<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Compose this trait for any command that needs access to the changelog file.
 */
trait GetChangelogFileTrait
{
    private function getChangelogFile(InputInterface $input) : string
    {
        return $input->getOption('file') ?: realpath(getcwd()) . '/CHANGELOG.md';
    }
}
