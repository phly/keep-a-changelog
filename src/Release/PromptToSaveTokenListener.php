<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Symfony\Component\Console\Question\ConfirmationQuestion;

class PromptToSaveTokenListener
{
    public function __invoke(SaveTokenEvent $event) : void
    {
        $question = new ConfirmationQuestion('Do you want to save this token for future use?', false);

        if (! $event->questionHelper()->ask($event->input(), $event->output(), $question)) {
            $event->abort();
        }
    }
}
