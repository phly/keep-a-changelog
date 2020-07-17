<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Provider\Milestone;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function count;
use function sprintf;

class ListMilestonesEvent extends AbstractMilestoneProviderEvent
{
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    /**
     * @var Milestone[]
     */
    public function milestonesRetrieved(array $milestones) : void
    {
        $output = $this->output();

        if (count($milestones) === 0) {
            $output->writeln('<info>No milestones discovered</info>');
            return;
        }

        $output->writeln('<info>Found the following milestones:</info>');

        foreach ($milestones as $milestone) {
            /** @var Milestone $milestone */
            $output->writeln(sprintf(
                '- (%d) %s: %s',
                $milestone->id(),
                $milestone->title(),
                $milestone->description() ?: '(no description)'
            ));
        }
    }

    public function errorListingMilestones(Throwable $e) : void
    {
        $this->failed = true;
        $output       = $this->output();

        $output->writeln('<error>Error listing milestone!</error>');
        $output->writeln('An error occurred when attempting to retrieve milestones from your provider:');
        $output->writeln('');
        $output->writeln('Error Message: ' . $e->getMessage());
    }
}
