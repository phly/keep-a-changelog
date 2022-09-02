<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

trait MaskProviderTokensTrait
{
    private function maskProviderTokens(array $config): array
    {
        if (! isset($config['providers'])) {
            return $config;
        }

        foreach ($config['providers'] as $name => $data) {
            $config['providers'][$name]['token'] = isset($data['token']) ? 'Yes' : 'No';
        }

        return $config;
    }
}
