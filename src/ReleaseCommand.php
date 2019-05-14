<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Phly\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function array_merge;
use function array_pop;
use function chmod;
use function count;
use function dirname;
use function exec;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_dir;
use function is_readable;
use function mkdir;
use function preg_match;
use function preg_quote;
use function sprintf;
use function strrpos;
use function strstr;
use function strtolower;
use function substr;

class ReleaseCommand extends Command
{
    use GetChangelogFileTrait;
    use GetConfigValuesTrait;
    use ProvideCommonOptionsTrait;

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
$HOME/.keep-a-changelog/config.ini or $HOME/.keep-a-changelog/token for the
value unless one is provided via the --token option.  When a token is provided
via the --token option, the tool will prompt you to ask if you wish to store
the token for later use.

When complete, the tool will provide a URL to the created release.

EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(string $name = 'release', ?EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: new EventDispatcher(new Release\ReleaseEventsProvider());
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription('Create a new release using the relevant changelog entry.');
        $this->setHelp(self::HELP);
        $this->addArgument(
            'package',
            InputArgument::REQUIRED,
            'Package to release; must be in org/repo format, and match the repository name;'
                . ' allows GitLab subgroup format'
        );
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'Version to tag'
        );
        $this->addOption(
            'token',
            't',
            InputOption::VALUE_REQUIRED,
            'Personal access token to use'
        );
        $this->addOption(
            'remote',
            'r',
            InputOption::VALUE_REQUIRED,
            'Git remote to push tag to; defaults to first Git remote matching provider and package'
        );
        $this->addOption(
            'tagname',
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

        $this->injectConfigBasedOptions();
    }

    /**
     * @throws Exception\ChangelogFileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $validator = $this->dispatcher->dispatch(new Release\ValidateRequirementsEvent($input, $output));
        if (! $validator->requirementsMet()) {
            return 1;
        }
        $version       = $validator->version();
        $tagName       = $validator->tagName();
        $config        = $validator->config();

        $output->writeln('<info>Preparing changelog for release</info>');
        $parser = $this->dispatcher->dispatch(new Release\PrepareChangelogEvent(
            $input,
            $output,
            $version
        ));
        if (! $parser->changelogIsReady()) {
            return 1;
        }
        $changelog = $parser->changelog();

        $tag = $this->dispatcher->dispatch(new Release\PushTagEvent(
            $input,
            $output,
            $this->getHelper('question'),
            $config,
            $tagName
        ));
        if (! $tag->wasPushed()) {
            return 1;
        }

        $release = $this->dispatcher->dispatch(new Release\CreateReleaseEvent(
            $input,
            $output,
            $config->provider(),
            $version,
            $changelog,
            $config->token()
        ));
        if (! $release->wasCreated()) {
            return 1;
        }

        $this->dispatcher->dispatch(new Release\SaveTokenEvent(
            $input,
            $output,
            $this->getHelper('question'),
            $config->token()
        ));

        $output->writeln(sprintf('<info>Created %s<info>', $release->release()));

        return 0;
    }
}
