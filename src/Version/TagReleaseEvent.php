<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogAwareEventInterface;
use Phly\KeepAChangelog\Common\ChangelogProviderTrait;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class TagReleaseEvent extends AbstractEvent implements
    ChangelogAwareEventInterface,
    DiscoverableVersionEventInterface
{
    use ChangelogProviderTrait;
    use DiscoverVersionEventTrait;
    use VersionValidationTrait;

    /** @var null|string */
    private $tagName;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        ?string $version,
        ?string $tagName
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
        $this->version    = $version;
        $this->tagName    = $tagName ?: $version;
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }

    public function package(): ?string
    {
        return $this->config()->package();
    }

    public function tagName(): string
    {
        return $this->tagName;
    }

    public function taggingComplete(): void
    {
        $this->output->writeln(sprintf(
            '<info>Created tag "%s" for package "%s" using the following notes:</info>',
            $this->tagName,
            $this->package()
        ));

        $this->output->write($this->changelog());
    }

    public function tagOperationFailed(): void
    {
        $this->failed = true;
        $this->output->writeln('<error>Error creating tag!</error>');
        $this->output->writeln('The "git tag" operation failed; check the output logs for details');
    }

    public function unversionedChangesPresent(): void
    {
        $this->failed = true;
        $this->output->writeln('<error>You have changes present in your tree that are not checked in.</error>');
        $this->output->writeln('Either check them in, or use the --force flag.');
    }

    public function changelogMissingDate(): void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Version %s does not have a release date associated with it!</error>',
            $this->version()
        ));
        $this->output->writeln('<error>You may need to run version:ready first</error>');
    }
}
