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

class NewCommand extends Command
{
    private const DESCRIPTION = 'Create a new changelog file.';

    private const HELP = <<<'EOH'
Create a new changelog file. If no --changelog option is provided, the
assumption is CHANGELOG.md in the current directory. If no
--initial-version is provided, the assumption is 0.1.0. If the file already
exists, you can use --overwrite to replace it.
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
            'initial-version',
            '-i',
            InputOption::VALUE_REQUIRED,
            'Initial version to provide in new changelog file; defaults to 0.1.0.'
        );
        $this->addOption(
            'overwrite',
            '-o',
            InputOption::VALUE_NONE,
            'Overwrite the changelog file, if exists'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
            ->dispatch(new CreateNewChangelogEvent(
                $input,
                $output,
                $this->dispatcher,
                $input->getOption('initial-version') ?: '0.1.0',
                $input->getOption('overwrite') ?: false
            ))
            ->failed()
            ? 1
            : 0;
    }
}
