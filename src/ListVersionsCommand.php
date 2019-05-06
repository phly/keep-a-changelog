<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListVersionsCommand extends Command
{
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'List all versions represented in the changelog file.';

    private const HELP = <<< 'EOH'
Lists all versions represented in the changelog file, along with associated
release dates.
EOH;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $changelogFile = $this->getChangelogFile($input);

        $output->writeln('<info>Found the following versions:</info>');
        foreach ((new ChangelogParser())->findAllVersions($changelogFile) as $version => $date) {
            $output->writeln(sprintf('- %s (release date: %s)', $version, $date));
        }

        return 0;
    }
}
