<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\ChangelogEntryDiscoveryTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class PromoteEvent extends AbstractEvent implements ChangelogEntryAwareEventInterface
{
    use ChangelogEntryDiscoveryTrait;

    /** @var string */
    private $newVersion;

    /** @var string */
    private $releaseDate;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        string $version,
        string $releaseDate
    ) {
        $this->input       = $input;
        $this->output      = $output;
        $this->dispatcher  = $dispatcher;
        $this->newVersion  = $version;
        $this->releaseDate = $releaseDate;
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }

    public function newVersion(): string
    {
        return $this->newVersion;
    }

    public function releaseDate(): string
    {
        return $this->releaseDate;
    }

    public function version(): string
    {
        return 'unreleased';
    }

    public function versionIsInvalid(string $version): void
    {
        // intentional no-op; never should get called
    }

    public function didNotPromote(): void
    {
        $this->failed = true;
        $this->output()->writeln('<error>Invalid date provided for release; must be in Y-m-d format</error>');
    }

    public function changelogReady(): void
    {
        $this->output->writeln(sprintf(
            '<info>Renamed Unreleased entry to "%s" with release date of "%s"</info>',
            $this->newVersion,
            $this->releaseDate
        ));
    }
}
