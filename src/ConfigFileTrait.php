<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Compose this trait for any command that needs access to the configuration file.
 */
trait ConfigFileTrait
{

    private function getConfigFile(InputInterface $input) : string
    {
        $useGlobal = $input->getOption('global') ?: false;

        if (! $useGlobal) {
            return realpath(getcwd()) . '/.keep-a-changelog.ini';
        }

        $home = getenv('HOME');
        return sprintf('%s/.keep-a-changelog/config.ini', $home);
    }

    private function getConfig(InputInterface $input) : Config
    {
        $configFile = $this->getConfigFile($input);
        if (! is_readable($configFile)) {
            $home = getenv('HOME');
            $tokenFile = sprintf('%s/.keep-a-changelog/token', $home);
            $token = '';
            if (is_readable($tokenFile)) {
                $token = trim(file_get_contents($tokenFile));
            }
            return new Config($token);
        }
        $ini = parse_ini_file($configFile);
        return new Config($ini['token'] ?? '', $ini['provider'] ?? Config::PROVIDER_GITHUB);
    }

    private function saveConfigFile(string $filename, Config $config) : bool
    {
        $data = $config->getArrayCopy();
        $ini = '';
        foreach ($data as $key => $value) {
            $ini .= "$key = $value" . PHP_EOL;
        }
        return file_put_contents($filename, $ini) !== false;
    }
}
