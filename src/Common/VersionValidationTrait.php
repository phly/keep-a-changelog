<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function sprintf;

/**
 * Provides an implementation of VersionAwareEventInterface.
 *
 * Assumes that the class it is mixed in to implements IOInterface, and uses
 * the $failed property to stop propagation.
 */
trait VersionValidationTrait
{
    /** @var null|string */
    private $version;

    public function version(): ?string
    {
        return $this->version;
    }

    public function versionIsInvalid(string $version): void
    {
        $this->failed = true;
        $this->output()->writeln(sprintf(
            '<error>Invalid version "%s"; must follow semantic versioning rules</error>',
            $version
        ));
    }
}
