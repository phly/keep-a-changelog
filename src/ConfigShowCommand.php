<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigShowCommand extends Command
{
    private const DESCRIPTION = 'Show existing configuration.';

    private const HELP = <<<'EOH'
Shows existing configuration. If --local is provided, it will show local
configuration. If --global is provided, it will show global configuration.
If both are provided, or no arguments are provided, it will show merged
configuration.

In all cases, it will NOT display provider token values; instead, it will
simply display if a token is present for a given provider.
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
            'Show configuration from the global configuration file ($XDG_CONFIG_HOME/keep-a-changelog.ini)'
        );
        $this->addOption(
            'local',
            'l',
            InputOption::VALUE_NONE,
            'Show configuration from the local configuration file (./.keep-a-changelog.ini)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
            ->dispatch(new ConfigCommand\ShowConfigEvent(
                $input,
                $output,
                $input->getOption('local') ?: false,
                $input->getOption('global') ?: false
            ))
            ->failed()
            ? 1
            : 0;
    }
}
