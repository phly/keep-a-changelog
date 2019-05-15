<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Symfony\Component\Console\Question\ChoiceQuestion;

class PromptForGitRemoteListener
{
    public function __invoke(RemoteNameDiscovery $event) : void
    {
        if ($event->remoteWasFound()) {
            return;
        }

        $choices = array_merge($event->remotes(), ['abort' => 'Abort release']);

        $helper   = $event->questionHelper();
        $question = new ChoiceQuestion(
            'More than one valid remote was found; which one should I use?',
            $choices
        );

        $remote = $helper->ask($event->input(), $event->output(), $question);

        if ('Abort release' === $remote) {
            $event->abort();
            return;
        }

        $event->foundRemote($remote);
    }
}
