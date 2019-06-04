<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider\ProviderSpec;

use function is_readable;
use function parse_ini_file;

use const INI_SCANNER_TYPED;

abstract class AbstractConfigListener
{
    /**
     * String used in error messages to indicate which config file
     * raised the error.
     *
     * @var string
     */
    protected $configType = 'global config file';

    /**
     * Flag indicating whether or not to consume provider tokens
     * defined in the config file. This should only be enabled for
     * global configuration files, as they are the only ones that
     * have appropriate user permissions set!
     *
     * @var bool
     */
    protected $consumeProviderTokens = true;

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
        $providerList = $config->providers();

        foreach ($providers as $name => $data) {
            $spec = $providerList->has($name) ? $providerList->get($name) : new ProviderSpec($name);

            if (isset($data['class'])) {
                $spec->setClassName($data['class']);
            }

            if (isset($data['url'])) {
                $spec->setUrl($data['url']);
            }

            if ($this->consumeProviderTokens && isset($data['token'])) {
                $spec->setToken($data['token']);
            }

            $providerList->add($spec);
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

        if (! isset($defaults['provider'])) {
            return;
        }

        if (! $config->providers()->has($defaults['provider'])) {
            throw Exception\InvalidProviderException::forMissingProvider(
                $defaults['provider'],
                $config->providers()->listKnownTypes()
            );
        }

        $config->setProviderName($defaults['provider']);
    }
}
