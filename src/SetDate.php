<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use stdClass;

class SetDate
{
    public function __invoke(string $changelogFile, string $date) : void
    {
        $contents = file($changelogFile);
        if (false === $contents) {
            throw Exception\ChangelogFileNotFoundException::at($changelogFile);
        }

        $indexData = $this->locateInjectionIndex($contents);
        if (null === $indexData->index) {
            throw Exception\NoMatchingChangelogDiscoveredException::for($changelogFile);
        }

        file_put_contents(
            $changelogFile,
            implode('', $this->injectDate($contents, $indexData, $date))
        );
    }

    private function locateInjectionIndex(array $contents) : stdClass
    {
        $action = (object) [
            'index' => null,
            'linePrefix' => '',
        ];

        // @codingStandardsIgnoreStart
        $regex = '/^(?P<prefix>## \d+\.\d+\.\d+(?:(alpha|beta|rc|dev|patch|pl|a|b|p)\d+)?)\s+-\s+(?:(?!\d{4}-\d{2}-\d{2}).*)/i';
        // @codingStandardsIgnoreEnd

        foreach ($contents as $index => $line) {
            if (! preg_match($regex, $line, $matches)) {
                continue;
            }

            $action->index = $index;
            $action->linePrefix = $matches['prefix'];
            break;
        }

        return $action;
    }

    private function injectDate(array $contents, stdClass $indexData, string $date) : array
    {
        $replacement = sprintf(
            "%s - %s\n",
            $indexData->linePrefix,
            $date
        );
        // Return value of array_splice is an array of the _replaced_ values,
        // not the array itself
        array_splice($contents, $indexData->index, 1, $replacement);
        return $contents;
    }
}
