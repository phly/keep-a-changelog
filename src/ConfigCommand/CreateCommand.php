<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    private const DESCRIPTION = 'Create a configuration file or files.';

    private const HELP = <<<'EOH'
Allows you to create and seed a configuration file.

If --local is provided, it will create local configuration in
./.keep-a-changelog.ini.
   
If --global is provided, it will create global configuration in
$XDG_CONFIG_HOME/keep-a-changelog.ini.

If --changelog is provided, that file will be used to seed the changelog_file
configuration setting.
EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, ?string $name = null)
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addOption(
            'global',
            'g',
            InputOption::VALUE_NONE,
            'Create the global configuration file ($XDG_CONFIG_HOME/keep-a-changelog.ini)'
        );
        $this->addOption(
            'local',
            'l',
            InputOption::VALUE_NONE,
            'Create the local configuration file (./.keep-a-changelog.ini)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
                ->dispatch(new CreateConfigEvent(
                    $input,
                    $output,
                    $input->getOption('local') ?: false,
                    $input->getOption('global') ?: false,
                    $input->getOption('changelog') ?: null
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
