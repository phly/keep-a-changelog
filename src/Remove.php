<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Output\OutputInterface;

class Remove
{
    use ChangelogEditorTrait;

    public function __invoke(OutputInterface $output, string $filename, string $version) : bool
    {
        $changelogData = $this->getChangelogEntry($filename, $version);
        if (! $changelogData) {
            $output->writeln(sprintf(
                '<error>Unable to identify a changelog entry for %s in %s; did you specify the correct file?</error>',
                $version,
                $filename
            ));
            return false;
        }

        $this->updateChangelogEntry($filename, '', $changelogData->index, $changelogData->length);

        return true;
    }
}
