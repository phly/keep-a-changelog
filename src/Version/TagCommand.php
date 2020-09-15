<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config\CommonConfigOptionsTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class TagCommand extends Command
{
    use CommonConfigOptionsTrait;

    private const HELP = <<<'EOH'
Create a new git tag for the current repository, using the relevant changelog entry.

Parses the CHANGELOG.md file and extracts the entry matching <version>; if no
matching version is found, or the entry does not have a date set, the tool will
raise an error.

Once extracted, the command runs "git tag -s <tagname>" using the following
message format:

    <package> <version>

    <changelog>

By default, the tool assumes that the current working directory is the package
name; if this is not the case, provide that optional argument when invoking the
tool.

NOTE: in some cases, you may need to run the following command to ensure
gpg operations (for signing tags) will work correctly:

    export GPG_TTY=$(tty)

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
        $this->setDescription('Create a new tag, using the relevant changelog entry.');
        $this->setHelp(self::HELP);
        $this->addArgument('version', InputArgument::REQUIRED, 'Version to tag');
        $this->addOption(
            'tagname',
            'a',
            InputOption::VALUE_REQUIRED,
            'Alternate git tag name to use when tagging; defaults to <version>'
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force tagging even if there are changes present in your tree'
        );

        $this->injectPackageOption($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = $input->getArgument('version');

        $output->writeln(sprintf('<info>Preparing to tag version %s</info>', $version));

        return $this->dispatcher
                ->dispatch(new TagReleaseEvent(
                    $input,
                    $output,
                    $this->dispatcher,
                    $version,
                    $input->getOption('tagname') ?: $version
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
