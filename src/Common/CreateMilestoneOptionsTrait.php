<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait CreateMilestoneOptionsTrait
{
    private function injectMilestoneOptions(Command $command): void
    {
        $command->addOption(
            'create-milestone',
            'm',
            InputOption::VALUE_NONE,
            'Create a milestone with your provider named after the new version'
        );

        $command->addOption(
            'create-milestone-with-name',
            null,
            InputOption::VALUE_REQUIRED,
            'Create a milestone with your provider using the provided name (instead of the version)'
        );
    }

    private function isMilestoneCreationRequested(InputInterface $input): bool
    {
        return $input->getOption('create-milestone')
            || $input->getOption('create-milestone-with-name');
    }

    private function getMilestoneName(InputInterface $input, string $default): string
    {
        return $input->getOption('create-milestone-with-name') ?: $default;
    }

    private function triggerCreateMilestoneEvent(
        string $name,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher
    ): CreateMilestoneEvent {
        $input = new ArrayInput(
            ['title' => $name],
            new InputDefinition([
                new InputArgument('title', InputArgument::REQUIRED),
                new InputArgument('description', InputArgument::OPTIONAL, '', ''),
            ])
        );

        return $dispatcher
            ->dispatch(new CreateMilestoneEvent(
                $input,
                $output,
                $dispatcher
            ));
    }
}
