<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

trait CommonOptionsTrait
{
    private function injectEditorOption(Command $command): void
    {
        $command->addOption(
            'editor',
            '-e',
            InputOption::VALUE_REQUIRED,
            'Provide the name of the editor program to use.'
        );
    }
}
