<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Edit;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EditorProviderTrait;
use Phly\KeepAChangelog\Common\VersionAwareEventInterface;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\ChangelogEntryDiscoverTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class EditChangelogEntryEvent extends AbstractEvent implements
    ChangelogEntryAwareEventInterface,
    EditorAwareEventInterface,
    VersionAwareEventInterface
{
    use ChangelogEntryDiscoverTrait;
    use EditorProviderTrait;
    use VersionValidationTrait;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        ?string $version,
        ?string $editor
    ) {
        $this->input   = $input;
        $this->output  = $output;
        $this->version = $version;
        $this->editor  = $editor;
    }
    
    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function editorFailed() : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Could not edit %s; please check the output for details.</error>',
            $this->changelogFile
        ));
    }

    public function editComplete() : void
    {
        $message = $this->version
            ? sprintf('<info>Edited change for version %s in %s</info>', $this->version, $this->changelogFile)
            : sprintf('<info>Edited most recent changelog in %s</info>', $this->changelogFile);
        $this->output->writeln($message);
    }
}
