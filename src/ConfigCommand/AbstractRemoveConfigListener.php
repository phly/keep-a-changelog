<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function file_exists;
use function sprintf;

abstract class AbstractRemoveConfigListener
{
    abstract public function configRemovalRequested(RemoveConfigEvent $event): bool;

    abstract public function getConfigFile(): string;

    public function __invoke(RemoveConfigEvent $event): void
    {
        if (! $this->configRemovalRequested($event)) {
            return;
        }

        $configFile = $this->getConfigFile();

        if (! file_exists($configFile)) {
            $event->configFileNotFound($configFile);
            return;
        }

        $output = $event->output();

        $output->writeln(sprintf(
            '<info>Found the following configuration file: %s</info>',
            $configFile
        ));

        $question = new ConfirmationQuestion('Do you really want to delete this file? ', false);

        if (! $this->getQuestionHelper()->ask($event->input(), $output, $question)) {
            $event->abort($configFile);
            return;
        }

        if (false === ($this->unlink)($configFile)) {
            $event->errorRemovingConfig($configFile);
            return;
        }

        $event->deletedConfigFile($configFile);
    }

    public function getQuestionHelper(): QuestionHelper
    {
        if ($this->questionHelper instanceof QuestionHelper) {
            return $this->questionHelper;
        }
        return new QuestionHelper();
    }

    /**
     * Provide a QuestionHelper instance for use in prompting the user for
     * confirmation.
     *
     * For testing purposes only.
     *
     * @internal
     *
     * @var null|QuestionHelper
     */
    public $questionHelper;

    /**
     * Provide a callable for removing a configuration file.
     *
     * For testing purposes only.
     *
     * @internal
     *
     * @var callable
     */
    public $unlink = 'unlink';
}
