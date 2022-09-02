<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\ChangelogEntryDiscoveryTrait;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EditorProviderTrait;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class EditChangelogVersionEvent extends AbstractEvent implements
    ChangelogEntryAwareEventInterface,
    EditorAwareEventInterface
{
    use ChangelogEntryDiscoveryTrait;
    use EditorProviderTrait;
    use VersionValidationTrait;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        ?string $version,
        ?string $editor
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
        $this->version    = $version;
        $this->editor     = $editor;
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }

    public function editorFailed(): void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Could not edit %s; please check the output for details.</error>',
            $this->config()->changelogFile()
        ));
    }

    public function editComplete(): void
    {
        $message = $this->version
            ? sprintf(
                '<info>Edited change for version %s in %s</info>',
                $this->version,
                $this->config()->changelogFile()
            )
            : sprintf(
                '<info>Edited most recent changelog in %s</info>',
                $this->config()->changelogFile()
            );
        $this->output->writeln($message);
    }
}
