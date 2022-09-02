<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use RuntimeException;

use function getenv;
use function rtrim;
use function sprintf;
use function strtr;

trait LocateGlobalConfigTrait
{
    /**
     * Set the global config root directory.
     *
     * For testing purposes only. Use this to set the config root to use
     * when attempting to find the config file.
     *
     * @internal
     *
     * @var null|string
     */
    public $configRoot;

    private function getConfigRoot(): string
    {
        if ($this->configRoot) {
            return $this->configRoot;
        }

        $configRoot = getenv('XDG_CONFIG_HOME');
        if ($configRoot) {
            return $this->normalizePath($configRoot);
        }

        $configRoot = getenv('HOME');
        if (! $configRoot) {
            throw new RuntimeException(
                'keep-a-changelog requires either the XDG_CONFIG_HOME or HOME'
                . ' env variables be set in order to operate.'
            );
        }

        return sprintf('%s/.config', $this->normalizePath($configRoot));
    }

    private function normalizePath(string $path): string
    {
        return rtrim(strtr($path, '\\', '/'), '/');
    }
}
