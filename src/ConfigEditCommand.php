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

class ConfigEditCommand extends Command
{
    use Common\CommonOptionsTrait;

    private const DESCRIPTION = 'Edit a configuration file.';

    private const HELP = <<<'EOH'
Allows you to edit a configuration file in $EDITOR or the binary provided
via the --editor option.

This command only allows editing one configuration at a time; you may
pass only --local OR --global when invoking it.
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
            'Edit the global configuration file ($XDG_CONFIG_HOME/keep-a-changelog.ini)'
        );
        $this->addOption(
            'local',
            'l',
            InputOption::VALUE_NONE,
            'Edit the local configuration file (./.keep-a-changelog.ini)'
        );

        $this->injectEditorOption($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
            ->dispatch(new ConfigCommand\EditConfigEvent(
                $input,
                $output,
                $input->getOption('local') ?: false,
                $input->getOption('global') ?: false,
                $input->getOption('editor') ?: null
            ))
            ->failed()
            ? 1
            : 0;
    }
}
