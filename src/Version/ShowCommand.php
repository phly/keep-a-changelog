<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends Command
{
    private const DESCRIPTION = 'Show the changelog entry for the given version.';

    private const HELP = <<<'EOH'
Opens the changelog and displays the entry for the given version.
EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, ?string $name = null)
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addArgument(
            'version',
            InputArgument::OPTIONAL,
            'Which version do you wish to display? (Defaults to latest)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->dispatcher
                ->dispatch(new ShowVersionEvent(
                    $input,
                    $output,
                    $this->dispatcher,
                    $input->getArgument('version') ?: null
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
