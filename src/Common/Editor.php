<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Symfony\Component\Console\Output\OutputInterface;

use function escapeshellarg;
use function sprintf;

use const STDERR;
use const STDIN;
use const STDOUT;

class Editor
{
    /**
     * Spawn an editor to edit the given filename.
     */
    public function spawnEditor(OutputInterface $output, string $editor, string $filename): int
    {
        $descriptorspec = [STDIN, STDOUT, STDERR];
        $command        = sprintf('%s %s', $editor, escapeshellarg($filename));

        $output->writeln(sprintf('<info>Executing "%s"</info>', $command));

        $pipes   = [];
        $process = ($this->procOpen)($command, $descriptorspec, $pipes);
        return ($this->procClose)($process);
    }

    /**
     * Specify a callback for opening a new process.
     *
     * For testing purposes only.
     *
     * @internal
     *
     * @var callable
     */
    public $procOpen = 'proc_open';

    /**
     * Specify a callback for closing an open process.
     *
     * For testing purposes only.
     *
     * @internal
     *
     * @var callable
     */
    public $procClose = 'proc_close';
}
