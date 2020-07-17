<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Config\CommonConfigOptionsTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    use CommonConfigOptionsTrait;

    private const HELP = <<<'END'
Create a milestone for the current repository via its provider.

The tool verifies that a package name and token are either provided via options
or configuration, and then creates a milestone using the given title, and, if
present, description.

END;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, string $name = 'milestone:create')
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription('Create a new milestone for this package via your provider');
        $this->setHelp(self::HELP);

        $this->addArgument('title', InputArgument::REQUIRED, 'Title/name of milestone');
        $this->addArgument('description', InputArgument::OPTIONAL, 'Milestone description');

        $this->injectPackageOption($this);
        $this->injectRemoteOption($this);
        $this->injectProviderOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
                ->dispatch(new CreateMilestoneEvent(
                    $input,
                    $output,
                    $this->dispatcher
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
