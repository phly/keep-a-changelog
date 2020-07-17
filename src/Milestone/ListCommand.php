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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    use CommonConfigOptionsTrait;

    private const HELP = <<<'END'
List milestones for the current repository via its provider.

Each item listed includes the milestone ID, title, and description; the
milestone ID can then be used later to close the milestone, if needed.

END;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, string $name = 'milestone:list')
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription('List milestones for this package via your provider');
        $this->setHelp(self::HELP);

        $this->injectPackageOption($this);
        $this->injectRemoteOption($this);
        $this->injectProviderOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
                ->dispatch(new ListMilestonesEvent(
                    $input,
                    $output,
                    $this->dispatcher
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
