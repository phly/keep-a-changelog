<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

class EntryTypes
{
    public const TYPE_ADDED      = 'added';
    public const TYPE_CHANGED    = 'changed';
    public const TYPE_DEPRECATED = 'deprecated';
    public const TYPE_REMOVED    = 'removed';
    public const TYPE_FIXED      = 'fixed';

    public const TYPES = [
        self::TYPE_ADDED,
        self::TYPE_CHANGED,
        self::TYPE_DEPRECATED,
        self::TYPE_REMOVED,
        self::TYPE_FIXED,
    ];
}
