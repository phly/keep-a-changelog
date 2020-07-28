<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function sprintf;

class CloseMilestoneEvent extends AbstractMilestoneProviderEvent
{
    /** @var int */
    private $id;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
        $this->id         = (int) $input->getArgument('id');
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function milestoneClosed(): void
    {
        $this->output()->writeln(sprintf(
            '<info>Closed milestone %d</info>',
            $this->id()
        ));
    }

    public function errorClosingMilestone(Throwable $e): void
    {
        $this->failed = true;
        $output       = $this->output();

        $output->writeln('<error>Error closing milestone!</error>');
        $output->writeln('An error occurred when attempting to close the milestone:');
        $output->writeln('');
        $output->writeln('Error Message: ' . $e->getMessage());
    }
}
