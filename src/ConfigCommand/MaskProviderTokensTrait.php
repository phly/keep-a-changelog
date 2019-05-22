<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

trait MaskProviderTokensTrait
{
    private function maskProviderTokens(array $config) : array
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
