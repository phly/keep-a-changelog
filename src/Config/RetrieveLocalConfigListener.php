<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;

use function getcwd;
use function realpath;

/**
 * Parses the local config to populate the Config instance.
 *
 * The local configuration file is an INI file with the following format:
 *
 * <code>
 * [defaults]
 * ; Note: the following item is the only one that differs from global config
 * package = some/package
 * changelog_file = changelog.md
 * provider = custom
 * remote = upstream
 *
 * [providers]
 * github[class] = Phly\KeepAChangelog\Provider\GitHub
 * github[url] = https://github.mwop.net
 * github[token] = this-is-a-token
 * custom[class] = Mwop\Git\Provider
 * custom[url] = https://git.mwop.net
 * custom[token] = this-is-a-token
 * gitlab[class] = Phly\KeepAChangelog\Provider\GitHub
 * gitlab[token] = this-is-a-token
 * </code>
 */
class RetrieveLocalConfigListener extends AbstractConfigListener
{
    protected $configType            = 'local config file';
    protected $consumeProviderTokens = false;

    protected function getConfigFile() : string
    {
        return realpath(getcwd()) . '/.keep-a-changelog.ini';
    }

    protected function processDefaults(array $defaults, Config $config) : void
    {
        parent::processDefaults($defaults, $config);

        if (! isset($defaults['package'])) {
            return;
        }

        $config->setPackage($defaults['package']);
    }
}
