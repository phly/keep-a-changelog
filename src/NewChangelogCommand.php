<?php
/**
 * @see       https://github.com/phly/keep-a-changelog-tagger for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog-tagger/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewChangelogCommand extends Command
{
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'Create a new changelog file.';

    private const HELP = <<< 'EOH'
Create a new changelog file. If no --file is provided, the assumption is
CHANGELOG.md in the current directory. If no --initial-version is
provided, the assumption is 0.1.0.
EOH;

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
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $file = $this->getChangelogFile($input);
        $version = $input->getOption('initial-version') ?: '0.1.0';

        (new NewChangelog())($file, $version);

        $output->writeln(sprintf(
            '<info>Created new changelog in file "%s" using initial version "%s".</info>',
            $file,
            $version
        ));

        return 0;
    }
}
