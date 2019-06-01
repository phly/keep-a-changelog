<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\ChangelogEntryDiscoveryTrait;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadyLatestChangelogEvent extends AbstractEvent implements ChangelogEntryAwareEventInterface
{
    use ChangelogEntryDiscoveryTrait;
    use VersionValidationTrait;

    /** @var string */
    private $releaseDate;
    
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        string $releaseDate,
        ?string $version = null
    ) {
        $this->input       = $input;
        $this->output      = $output;
        $this->dispatcher  = $dispatcher;
        $this->releaseDate = $releaseDate;
        $this->version     = $version;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function releaseDate() : string
    {
        return $this->releaseDate;
    }

    public function malformedReleaseLine(string $versionLine) : void
    {
        $this->failed = true;
        $this->output->writeln(
            '<error>Unable to set release date; most recent release has a malformed release line.</error>'
        );
        $this->output->writeln('Must be in the following format (minus initial indentation):');
        $this->output->writeln('  ## <version> - TBD');
        $this->output->writeln('where <version> follows semantic versioning rules.');
        $this->output->writeln('');
        $this->output->writeln('Discovered:');
        $this->output->writeln(sprintf('  %s', $versionLine));
    }

    public function changelogReady() : void
    {
        $versionString = $this->version
            ? sprintf('changelog version %s', $this->version)
            : 'most recent changelog';
        $this->output->writeln(sprintf(
            '<info>Set release date of %s to "%s"</info>',
            $versionString,
            $this->releaseDate
        ));
    }
}
