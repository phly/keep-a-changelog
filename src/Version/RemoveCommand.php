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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommand extends Command
{
    private const DESCRIPTION = 'Remove a changelog release version and its entries.';

    private const HELP = <<<'EOH'
Remove the given changelog release version and its entries based on the
<version> provided.  The command will provide a preview, and prompt for
confirmation before doing so (unless using the --force-removal flag).
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
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'The changelog version to remove.'
        );
        $this->addOption(
            'force-removal',
            'r',
            InputOption::VALUE_NONE,
            'Do not prompt for confirmation.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
            ->dispatch(new RemoveChangelogVersionEvent(
                $input,
                $output,
                $this->dispatcher,
                $input->getArgument('version')
            ))
            ->failed()
            ? 1
            : 0;
    }
}
