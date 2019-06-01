<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function date;

class ReadyCommand extends Command
{
    private const DESCRIPTION = 'In the latest changelog release entry, mark the entry ready by setting its release date.';

    private const HELP = <<<'EOH'
In the latest changelog release entry, mark the entry ready by setting its
release date.

If no --date is specified, the current date in YYYY-MM-DD format will be used.
EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, ?string $name = null)
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addOption(
            'date',
            '-d',
            InputOption::VALUE_REQUIRED,
            'Specific date string to use; use this if the date is other than today,'
                . ' or if you wish to use a different date format.'
        );
        $this->addOption(
            'date',
            '-d',
            InputOption::VALUE_REQUIRED,
            'Specific date string to use; use this if the date is other than today,'
                . ' or if you wish to use a different date format.'
        );
        $this->addOption(
            'release-version',
            'r',
            InputOption::VALUE_REQUIRED,
            'A specific changelog version to ready for release.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
            ->dispatch(new ReadyLatestChangelogEvent(
                $input,
                $output,
                $this->dispatcher,
                $input->getOption('date') ?: date('Y-m-d'),
                $input->getOption('release-version') ?: null
            ))
            ->failed()
            ? 1
            : 0;
    }
}
