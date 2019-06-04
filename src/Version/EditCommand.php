<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\CommonOptionsTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommand extends Command
{
    use CommonOptionsTrait;

    private const DESCRIPTION = 'Edit a changelog entry using the system editor.';

    private const HELP = <<<'EOH'
Edit a changelog entry using the system editor ($EDITOR), or the
editor provided via --editor.

If no <version> is provided, assumes the most recent changelog should be used.

By default, the command will edit CHANGELOG.md in the current directory, unless
a different file is specified via the --file option.
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
            InputArgument::OPTIONAL,
            'A specific changelog version to edit; defaults to latest.'
        );

        $this->injectEditorOption($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
                ->dispatch(new EditChangelogVersionEvent(
                    $input,
                    $output,
                    $this->dispatcher,
                    $input->getArgument('version') ?: null,
                    $input->getOption('editor') ?: null
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
