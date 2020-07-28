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

class CloseCommand extends Command
{
    use CommonConfigOptionsTrait;

    private const HELP = <<<'END'
Close a milestone for the current repository via its provider.

The tool verifies that a package name and token are either provided via options
or configuration, and then closes a milestone using the given milestone id.

NOTE: the milestone id is NOT its name, but the ID as presented in the
milestone:list command.

END;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, string $name = 'milestone:close')
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Close a milestone for this package via your provider');
        $this->setHelp(self::HELP);

        $this->addArgument('id', InputArgument::REQUIRED, 'Milestone ID');

        $this->injectPackageOption($this);
        $this->injectRemoteOption($this);
        $this->injectProviderOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->dispatcher
                ->dispatch(new CloseMilestoneEvent(
                    $input,
                    $output,
                    $this->dispatcher
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
