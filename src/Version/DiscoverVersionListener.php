<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogParser;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function array_shift;
use function count;
use function is_string;
use function preg_match;
use function sprintf;

use const PHP_EOL;

class DiscoverVersionListener
{
    /**
     * Used for testing
     *
     * @internal
     *
     * @var null|QuestionHelper
     */
    public $questionHelper;

    public function __invoke(DiscoverableVersionEventInterface $event): void
    {
        $version = $event->version();
        if (is_string($version) && ! empty($version)) {
            // Version was provided already
            return;
        }

        $readyVersions = $this->getReadyVersions(
            (new ChangelogParser())
                ->findAllVersions($event->config()->changelogFile())
        );

        if (0 === count($readyVersions)) {
            // No versions found; let version validator flag it as invalid
            return;
        }

        $versionToTag = array_shift($readyVersions);
        $question     = new ConfirmationQuestion(sprintf(
            "Most recent version in changelog file is <info>%s</info>; use this version? [Y/n]" . PHP_EOL . "> ",
            $versionToTag
        ));

        $questionHelper = $this->questionHelper ?: new QuestionHelper();
        if (! $questionHelper->ask($event->input(), $event->output(), $question)) {
            $event->versionNotAccepted();
            return;
        }

        $event->foundVersion($versionToTag);
    }

    private function getReadyVersions(iterable $versions): array
    {
        $readyVersions = [];

        foreach ($versions as $version => $date) {
            if (! preg_match('/^\d{4}-\d{2}\-\d{2}$/', $date)) {
                continue;
            }

            $readyVersions[] = $version;
        }

        return $readyVersions;
    }
}
