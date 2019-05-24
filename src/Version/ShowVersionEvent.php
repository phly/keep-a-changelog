<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\VersionAwareEventInterface;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowVersionEvent extends AbstractEvent implements VersionAwareEventInterface
{
    use VersionValidationTrait;

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

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function changelogVersionNotFound() : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Unable to find changelog for version %s in file %s</error>',
            $this->version,
            $this->config()->changelogFile()
        ));
    }

    public function changelogMissingDate() : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Changelog for version %s in file %s does not have a valid date associated</error>',
            $this->version,
            $this->config()->changelogFile()
        ));
    }

    public function changelogMalformed()
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Changelog for version %s in file %s is malformed</error>',
            $this->version,
            $this->config()->changelogFile()
        ));
    }
}
