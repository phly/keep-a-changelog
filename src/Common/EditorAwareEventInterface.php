<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

interface EditorAwareEventInterface
{
    /**
     * Access the currently selected editor value, if any.
     */
    public function editor(): ?string;

    /**
     * Indicate an editor was discovered, and set the internal editor value.
     */
    public function discoverEditor(string $editor): void;
}
