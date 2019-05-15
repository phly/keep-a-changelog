<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Provider\ProviderInterface;
use RuntimeException;

use function class_exists;
use function is_readable;
use function parse_ini_file;

use const INI_SCANNER_TYPED;

abstract class AbstractConfigListener
{
    /** @var string */
    protected $configType = 'global config file';

    abstract protected function getConfigFile() : string;

    public function __invoke(ConfigDiscovery $event) : void
    {
        $configFile = $this->getConfigFile();
        if (! is_readable($configFile)) {
            // No global config file; nothing to do
            return;
        }

        $data   = parse_ini_file($configFile, true, INI_SCANNER_TYPED);
        $config = $event->config();

        if (isset($data['providers'])) {
            $this->processProviders($data['providers'], $config);
        }

        if (isset($data['defaults'])) {
            $this->processDefaults($data['defaults'], $config);
        }
    }

    private function processProviders(array $providers, Config $config) : void
    {
        foreach ($providers as $name => $data) {
            if (! isset($data['class'])) {
                throw Exception\InvalidProviderException::forMissingClassName($name, $this->configType);
            }

            if (! class_exists($data['class'])) {
                throw Exception\InvalidProviderException::forMissingClass($name, $data['class'], $this->configType);
            }

            $provider = new $data['class']();
            if (! $provider instanceof ProviderInterface) {
                throw Exception\InvalidProviderException::forInvalidClass($name, $data['class'], $this->configType);
            }

            if (isset($data['url'])) {
                $provider->setUrl($data['url']);
            }

            if (isset($data['token'])) {
                $provider->setToken($data['token']);
            }

            $config->providers()->set($name, $provider);
        }
    }

    protected function processDefaults(array $defaults, Config $config)
    {
        if (isset($defaults['changelog_file'])) {
            $config->setChangelogFile($defaults['changelog_file']);
        }

        if (isset($defaults['remote'])) {
            $config->setRemote($defaults['remote']);
        }

        if (array_key_exists('prompt_to_save_token', $defaults)) {
            $defaults['prompt_to_save_token']
                ? $config->shouldPromptToSaveToken()
                : $config->shouldNotPromptToSaveToken();
        }

        if (! isset($defaults['provider'])) {
            return;
        }

        if (! $config->providers()->has($defaults['provider'])) {
            throw Exception\InvalidProviderException::forMissingProvider(
                $defaults['provider'],
                $config->providers()->listKnownTypes()
            );
        }

        $config->setProvider($config->providers->get($defaults['provider']));
    }
}
