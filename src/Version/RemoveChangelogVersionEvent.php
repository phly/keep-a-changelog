<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\ChangelogEntryDiscoveryTrait;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class RemoveChangelogVersionEvent extends AbstractEvent implements
    ChangelogEntryAwareEventInterface
{
    use ChangelogEntryDiscoveryTrait;
    use VersionValidationTrait;

    /** @var bool */
    private $aborted = false;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        string $version
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
        $this->version    = $version;
    }

    public function isPropagationStopped(): bool
    {
        return $this->aborted || $this->failed;
    }

    public function abort()
    {
        $this->aborted = true;
        $this->output->writeln('<info>Aborting at user request</info>');
    }

    public function versionRemoved()
    {
        $this->output->writeln(sprintf(
            '<info>Removed changelog version %s from file %s.</info>',
            $this->version,
            $this->config()->changelogFile()
        ));
    }
}
