<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\NewChangelog;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\VersionAwareEventInterface;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateNewChangelogEvent extends AbstractEvent implements VersionAwareEventInterface
{
    use VersionValidationTrait;

    /** @var bool */
    private $overwrite;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        string $initialVersion,
        bool $overwrite
    ) {
        $this->input     = $input;
        $this->output    = $output;
        $this->version   = $initialVersion;
        $this->overwrite = $overwrite;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function overwrite() : bool
    {
        return $this->overwrite;
    }

    public function changelogExists() : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Cannot create changelog file "%s"; file exists.</error>',
            $this->changelogFile
        ));
        $this->output->writeln('If you want to overwrite the file, use the --overwrite|-o option');
    }

    public function createdChangelog() : void
    {
        $this->output->writeln(sprintf(
            '<info>Created new changelog in file "%s" using initial version "%s".</info>',
            $this->changelogFile,
            $this->version
        ));
    }
}
