<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Unreleased;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function date;

class PromoteCommand extends Command
{
    private const DESCRIPTION = 'Give a name to an unreleased version.';

    private const HELP = <<<'EOH'
Renames the current Unreleased version to the <version> provided, and sets the
release date to today (unless the --date|-d option is provided).

EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, string $name = 'unreleased:promote')
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);

        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'The version to promote the unreleased version to.'
        );

        $this->addOption(
            'date',
            'd',
            InputOption::VALUE_REQUIRED,
            'Specific release date to use',
            date('Y-m-d')
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
                ->dispatch(new PromoteEvent(
                    $input,
                    $output,
                    $this->dispatcher,
                    $input->getArgument('version'),
                    $input->getOption('date')
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
