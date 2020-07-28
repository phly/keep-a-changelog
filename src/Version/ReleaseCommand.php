<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config\CommonConfigOptionsTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseCommand extends Command
{
    use CommonConfigOptionsTrait;

    private const HELP = <<<'EOH'
Create a release using the changelog entry for the specified version.

The tool first checks to ensure we have a tag for the given version; if not,
it raises an error.

It then parses the CHANGELOG.md file and extracts the entry matching <version>;
if no matching version is found, or the entry does not have a date set, the
tool will raise an error.

Once extracted, the command pushes the tag to the remote specified, using the
tagname if provided (as tags and release versions may differ; e.g.,
"release-2.4.7", "v3.8.1", etc.).

It then attempts to create a release on the specified provider, using the provided 
package name and version. To do this, the tool requires that you have created and 
registered a personal access token in the provider. The tool will look in 
$XDG_CONFIG_HOME/keep-a-changelog.ini or ./.keep-a-changelog.ini for the
value unless one is provided via the --provider-token option.

When complete, the tool will provide a URL to the created release.

EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, string $name = 'release')
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Create a new release using the relevant changelog entry.');
        $this->setHelp(self::HELP);
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'Version to release'
        );
        $this->addOption(
            'tag-name',
            'a',
            InputOption::VALUE_REQUIRED,
            'Alternate git tag name matching the release to push; defaults to <version>'
        );
        $this->addOption(
            'name',
            null,
            InputOption::VALUE_REQUIRED,
            'Name of release to create; defaults to "<package> <version>"'
        );

        $this->injectPackageOption($this);
        $this->injectRemoteOption($this);
        $this->injectProviderOptions($this);
    }

    /**
     * @throws Exception\ChangelogFileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->dispatcher
                ->dispatch(new ReleaseEvent($input, $output, $this->dispatcher))
                ->failed()
                    ? 1
                    : 0;
    }
}
