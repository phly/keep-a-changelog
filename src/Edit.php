<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Output\OutputInterface;

class Edit
{
    use ChangelogEditorTrait;

    public function __invoke(
        OutputInterface $output,
        string $filename,
        ?string $editor,
        ?string $version = null
    ) : bool {
        $changelogData = $this->getChangelogEntry($filename, $version);
        if (! $changelogData) {
            $output->writeln(sprintf(
                '<error>Unable to identify a changelog entry in %s; did you specify the correct file?</error>',
                $filename
            ));
            return false;
        }

        $editor   = $editor ?: $this->discoverEditor();
        $tempFile = $this->createTempFileWithContents($changelogData->contents);

        $status = $this->spawnEditor($output, $editor, $tempFile);

        if (0 !== $status) {
            $output->writeln(sprintf(
                '<error>Unable to update %s; editor returned non-success value</error>',
                $filename
            ));
            return false;
        }

        $this->updateChangelogEntry(
            $filename,
            file_get_contents($tempFile),
            $changelogData->index,
            $changelogData->length
        );
        return true;
    }

    /**
     * Creates a temporary file with the changelog contents.
     */
    private function createTempFileWithContents(string $contents) : string
    {
        $filename = sprintf('%s.md', uniqid('KAC', true));
        $path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
        file_put_contents($path, $contents);
        return $path;
    }

    /**
     * Determines the system editor command and returns it.
     *
     * Checks for the $EDITOR env variable, returning its value if present.
     *
     * If not, it checks to see if we are on a Windows or other type of system,
     * returning "notepad" or "vi", respecively.
     */
    private function discoverEditor() : string
    {
        $editor = getenv('EDITOR');

        if ($editor) {
            return $editor;
        }

        return isset($_SERVER['OS']) && false !== strpos($_SERVER['OS'], 'indows')
            ? 'notepad'
            : 'vi';
    }

    /**
     * Spawn an editor to edit the given filename.
     */
    public function spawnEditor(OutputInterface $output, string $editor, string $filename) : int
    {
        $descriptorspec = [STDIN, STDOUT, STDERR];
        $command = sprintf('%s %s', $editor, escapeshellarg($filename));

        $output->writeln(sprintf('<info>Executing "%s"</info>', $command));

        $process = proc_open($command, $descriptorspec, $pipes);
        return proc_close($process);
    }
}
