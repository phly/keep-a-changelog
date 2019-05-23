<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Symfony\Component\Console\Output\OutputInterface;

use function escapeshellarg;
use function sprintf;
use function proc_close;
use function proc_open;

class Editor
{
    /**
     * Spawn an editor to edit the given filename.
     */
    public function spawnEditor(OutputInterface $output, string $editor, string $filename) : int
    {
        $descriptorspec = [STDIN, STDOUT, STDERR];
        $command        = sprintf('%s %s', $editor, escapeshellarg($filename));

        $output->writeln(sprintf('<info>Executing "%s"</info>', $command));

        $process = proc_open($command, $descriptorspec, $pipes);
        return proc_close($process);
    }
}
