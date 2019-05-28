<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PromptForRemovalConfirmationListener
{
    public function __invoke(RemoveChangelogVersionEvent $event) : void
    {
        $input = $event->input();
        if ($input->hasOption('force-removal') && $input->getOption('force-removal')) {
            // No need to prompt
            return;
        }

        $entry  = $event->changelogEntry();
        $output = $event->output();

        $output->writeln('<info>Found the following entry:</info>');
        $output->writeln($entry->contents);

        $helper   = new QuestionHelper();
        $question = new ConfirmationQuestion('Do you really want to delete this version ([y]es/[n]o)? ', false);

        if (! $helper->ask($input, $output, $question)) {
            $event->abort();
            return;
        }
    }
}
