<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use stdClass;

/**
 * Add an entry to the latest changelog.
 */
class AddEntry
{
    public const TYPE_ADDED = 'added';
    public const TYPE_CHANGED = 'changed';
    public const TYPE_DEPRECATED = 'deprecated';
    public const TYPE_REMOVED = 'removed';
    public const TYPE_FIXED = 'fixed';

    public const TYPES = [
        self::TYPE_ADDED,
        self::TYPE_CHANGED,
        self::TYPE_DEPRECATED,
        self::TYPE_REMOVED,
        self::TYPE_FIXED,
    ];

    private const ACTION_INJECT = 'inject';
    private const ACTION_REPLACE = 'replace';
    private const ACTION_NOT_FOUND = 'not-found';
    private const APPEND_NEWLINE = true;

    /**
     * Add an entry to the latest changelog.
     *
     * Finds the section of the latest changelog version corresponding to $type
     * and injects $entry to the top of that section, writing the changes to
     * the $changelogFile when done.
     *
     * @throws Exception\InvalidEntryTypeException
     * @throws Exception\ChangelogFileNotFoundException
     */
    public function __invoke(string $type, string $changelogFile, string $entry) : void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw Exception\InvalidEntryTypeException::forType($type);
        }

        $contents = file($changelogFile);
        if (false === $contents) {
            throw Exception\ChangelogFileNotFoundException::at($changelogFile);
        }

        file_put_contents(
            $changelogFile,
            implode(
                '',
                $this->injectEntry(
                    $contents,
                    $this->locateInjectionIndex($contents, $type),
                    $entry
                )
            )
        );
    }

    /**
     * Locates the location within the changelog where the injection should occur.
     * Also determines if the injection is a replacement or an addition.
     */
    private function locateInjectionIndex(array $contents, string $type) : stdClass
    {
        $action = (object) [
            'index' => null,
            'type' => self::ACTION_NOT_FOUND,
        ];

        foreach ($contents as $index => $line) {
            if (! preg_match('/^### ' . $type . '/i', $line)) {
                continue;
            }

            $action->index = $index + 2;
            $action->type = preg_match('/^- Nothing/', $contents[$action->index])
                ? self::ACTION_REPLACE
                : self::ACTION_INJECT;
            break;
        }

        return $action;
    }

    /**
     * Injects the new entry at the detected index, replacing the line if required.
     */
    private function injectEntry(array $contents, stdClass $action, string $entry) : array
    {
        switch ($action->type) {
            case self::ACTION_REPLACE:
                array_splice($contents, $action->index, 1, $this->formatEntry($entry));
                break;
            case self::ACTION_INJECT:
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
