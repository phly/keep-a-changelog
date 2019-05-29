<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

use function array_splice;
use function file;
use function file_put_contents;
use function implode;
use function in_array;
use function preg_match;
use function preg_replace;
use function sprintf;

class AddChangelogEntryListener
{
    public const APPEND_NEWLINE = true;

    public function __invoke(AddChangelogEntryEvent $event) : void
    {
        $entryType = $event->entryType();
        if (! in_array($entryType, EntryTypes::TYPES, true)) {
            $event->typeIsInvalid();
            return;
        }

        $changelogFile  = $event->config()->changelogFile();
        $contents       = file($changelogFile);
        $injectionIndex = $this->locateInjectionIndex($contents, $entryType);

        if ($injectionIndex->type === InjectionIndex::ACTION_NOT_FOUND) {
            $event->matchingEntryTypeNotFound();
            return;
        }

        file_put_contents(
            $changelogFile,
            implode('', $this->injectEntry(
                $contents,
                $injectionIndex,
                $event->entry()
            ))
        );

        $event->addedChangelogEntry($changelogFile, $entryType);
    }

    /**
     * Locates the location within the changelog where the injection should occur.
     * Also determines if the injection is a replacement or an addition.
     */
    private function locateInjectionIndex(array $contents, string $type) : InjectionIndex
    {
        $action = new InjectionIndex();

        foreach ($contents as $index => $line) {
            if (! preg_match('/^### ' . $type . '/i', $line)) {
                continue;
            }

            $action->index = $index + 2;
            $action->type = preg_match('/^- Nothing/', $contents[$action->index])
                ? InjectionIndex::ACTION_REPLACE
                : InjectionIndex::ACTION_INJECT;
            break;
        }

        return $action;
    }

    /**
     * Injects the new entry at the detected index, replacing the line if required.
     */
    private function injectEntry(array $contents, InjectionIndex $action, string $entry) : array
    {
        switch ($action->type) {
            case InjectionIndex::ACTION_REPLACE:
                array_splice($contents, $action->index, 1, $this->formatEntry($entry));
                break;
            case InjectionIndex::ACTION_INJECT:
                array_splice($contents, $action->index, 0, $this->formatEntry($entry, self::APPEND_NEWLINE));
                break;
            default:
                break;
        }
        return $contents;
    }

    /**
     * Formats the entry for use in the changelog.
     *
     * Prepends the string '- ', and appends a newline if none is present.
     *
     * If $withExtraLine is true, an extra newline is appended.
     *
     * If the string spans multiple lines, it ensures all additional lines are
     * indented two characters.
     */
    private function formatEntry(string $entry, bool $withExtraLine = false) : string
    {
        $entry = sprintf('- %s', $entry);
        $entry = preg_replace("/\n(?!\s{2}|$)/s", "\n  ", $entry);
        if ("\n" !== $entry[-1]) {
            // All entries need to end with a new line.
            $entry .= "\n";
        }
        if ($withExtraLine) {
            // If an extra line is requested, append it
            $entry .= "\n";
        }
        return $entry;
    }
}
