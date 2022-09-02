<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditLinksCommand extends Command
{
    private const DESCRIPTION = 'Edit the links section of a changelog file.';

    private const HELP = <<<'EOH'
Changelog files can optionally have a section of links at the end of the file
in the following format:

    [link name]: <url>

(Indentation is for documentation purposes; omit it in actual files.)

This command will spawn your $EDITOR with any discovered links, and then ensure
they are written back to the file on completion of any edits.
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->dispatcher
                ->dispatch(new EditChangelogLinksEvent(
                    $input,
                    $output,
                    $this->dispatcher
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
