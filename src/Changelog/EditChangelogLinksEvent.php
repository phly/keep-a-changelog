<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EditorProviderTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class EditChangelogLinksEvent extends AbstractEvent implements EditorAwareEventInterface
{
    use EditorProviderTrait;

    /**
     * If no links are discovered in the changelog file, we will be appending
     * them to any existing content, instead of splicing them in.
     *
     * @var bool
     */
    private $appendLinksToChangelogFile = false;

    /**
     * Value object representing the discovered links from the changelog file.
     *
     * @var null|ChangelogEntry
     */
    private $links;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }

    public function appendLinksToChangelogFile(): bool
    {
        return $this->appendLinksToChangelogFile;
    }

    public function links(): ?ChangelogEntry
    {
        return $this->links;
    }

    public function discoveredLinks(ChangelogEntry $links): void
    {
        $this->links = $links;
    }

    public function noLinksDiscovered(): void
    {
        $this->appendLinksToChangelogFile = true;
    }

    public function editComplete(string $changelogFile): void
    {
        $this->output->writeln(sprintf(
            '<info>Completed editing links for file %s</info>',
            $changelogFile
        ));
    }

    public function editFailed(string $changelogFile): void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Editing links for file %s failed</error>',
            $changelogFile
        ));
        $this->output->writeln('Review the output above for potential errors.');
    }
}
