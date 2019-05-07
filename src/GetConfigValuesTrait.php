<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Compose this trait for any command that needs access to the repository provider.
 */
trait GetConfigValuesTrait
{
    use ConfigFileTrait;

    /**
     * @var ?ProviderInterface
     */
    private $provider = null;

    private function prepareConfig(
        InputInterface $input,
        string $tokenOptionName = 'token',
        string $providerOptionName = 'provider',
        string $providerDomainOptionName = 'provider-domain'
    ) : Config {
        $config = $this->getConfig($input);

        $token  = $input->hasOption($tokenOptionName) ? $input->getOption($tokenOptionName) : null;
        $config = $token ? $config->withToken($token) : $config;

        $provider = $input->getOption($providerOptionName);
        $config = $provider ? $config->withProvider($provider) : $config;

        $domain = $input->getOption($providerDomainOptionName);
        $config = $config->withDomain((string) $domain);

        return $config;
    }

    private function getDomain(Config $config) : string
    {
        return trim($config->domain());
    }

    private function getProvider(Config $config) : Provider\ProviderInterface
    {
        if ($this->provider instanceof Provider\ProviderInterface) {
            return $this->provider;
        }

        $provider = trim($config->provider());

        switch ($provider) {
            case 'github':
                $this->provider = (new Provider\GitHub())->withDomainName($this->getDomain($config));
                break;
            case 'gitlab':
                $this->provider = (new Provider\GitLab())->withDomainName($this->getDomain($config));
                break;
            default:
                throw Exception\InvalidProviderException::forProvider($provider);
                break;
        }

        return $this->provider;
    }

    private function getToken(Config $config, InputInterface $input, OutputInterface $output) : ?string
    {
        $token = $config->token();
        if ($token) {
            return trim($token);
        }

        $configFile = $this->getConfigFile($input);
        $output->writeln(sprintf(
            '<error>No token provided, and could not find it in the config file %s</error>',
            $configFile
        ));
        $output->writeln(
            'Please provide the --token option, or create the config file with the config command'
        );

        return null;
    }
}
