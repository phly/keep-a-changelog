<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ReleaseCommand extends Command
{
    use GetChangelogFileTrait;
    use GetConfigValuesTrait;
    use ProvideCommonOptionsTrait;

    private const HELP = <<< 'EOH'
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

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $version = $input->getArgument('version');
        $tagName = $input->getOption('tagname') ?: $version;

        $this->verifyTagExists($tagName);

        $config  = $this->prepareConfig($input);
        $package = $input->getArgument('package');

        $token = $this->getToken($config, $input, $output);
        if (! $token) {
            return 1;
        }

        $changelogFile = $this->getChangelogFile($input);
        if (! is_readable($changelogFile)) {
            throw Exception\ChangelogFileNotFoundException::at($changelogFile);
        }

        $output->writeln('<info>Preparing changelog for release</info>');

        $parser    = new ChangelogParser();
        $changelog = $parser->findChangelogForVersion(
            file_get_contents($changelogFile),
            $version
        );

        $formatter = new ChangelogFormatter();
        $changelog = $formatter->format($changelog);

        $remotes = $this->fetchGitRemotes();
        if (! $remotes) {
            $output->writeln('<error>Cannot determine remote to which to push tag!</error>');
            $output->writeln(
                'The command "git remote -v" had a non-zero exit status; verify the command works, and try again.'
            );
            return 1;
        }

        $provider = $this->getProvider($config);

        $remote   = $input->getOption('remote') ?: $this->lookupRemote(
            $input,
            $output,
            $provider,
            $package,
            $remotes
        );

        if (! $remote) {
            return 1;
        }

        $output->writeln(sprintf(
            '<info>Pushing tag %s to %s</info>',
            $version,
            $remote
        ));

        if (! $this->pushTag($tagName, $remote)) {
            $output->writeln('<error>Error pushing tag to remote!');
            $output->writeln('Please check the output for details.');
            return 1;
        }

        $releaseName = $this->createReleaseName($input, $package, $version);
        $output->writeln(sprintf(
            '<info>Creating release "%s"</info>',
            $releaseName
        ));

        $release = $provider->createRelease(
            $package,
            $releaseName,
            $tagName,
            $changelog,
            $token
        );
        if (! $release) {
            $output->writeln('<error>Error creating release!</error>');
            $output->writeln('Check the output logs for details, or re-run this command with verbosity turned on');
            return 1;
        }

        if ($input->getOption('token')) {
            $this->promptToSaveToken($token, $input, $output);
        }

        $output->writeln(sprintf('<info>Created %s<info>', $release));

        return 0;
    }

    private function promptToSaveToken(string $token, InputInterface $input, OutputInterface $output) : void
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to save this token for future use?', false);

        if (! $helper->ask($input, $output, $question)) {
            return;
        }

        $home = getenv('HOME');
        $tokenFile = sprintf('%s/.keep-a-changelog/token', $home);

        if (! is_dir(dirname($tokenFile))) {
            mkdir(dirname($tokenFile), 0700, true);
        }

        file_put_contents($tokenFile, $token);
        chmod($tokenFile, 0600);
    }

    private function createReleaseName(InputInterface $input, string $package, string $version) : string
    {
        $name = $input->getOption('name');
        if ($name) {
            return $name;
        }
        $lastSeparator = strrpos($package, '/');
        $repo          = substr($package, $lastSeparator + 1);
        return sprintf('%s %s', $repo, $version);
    }

    private function verifyTagExists($version) : void
    {
        $command = sprintf('git show %s', $version);
        exec($command, $output, $return);
        if (0 !== $return) {
            throw Exception\MissingTagException::forVersion($version);
        }
    }

    private function pushTag(string $version, string $remote) : bool
    {
        $command = sprintf('git push %s %s', $remote, $version);
        exec($command, $output, $return);
        return 0 === $return;
    }

    /**
     * Determine which remote to which to push a tag
     *
     * This method uses the provider and package, looping through the remotes
     * returned by `git remote -v` to match each against remotes configured
     * for push operations. If the remote matches both the provider domain and
     * the package name, then it will return the remote name; otherwise, it
     * returns null, indicating none could be found.
     */
    private function lookupRemote(
        InputInterface $input,
        OutputInterface $output,
        Provider\ProviderInterface $provider,
        string $package,
        array $remotes
    ) : ?string {
        $domain      = $this->getProviderDomain($provider);
        $domainRegex = '#[/@.]' . preg_quote($domain) . '(:\d+:|:|/)#i';
        $discovered  = [];

        foreach ($remotes as $line) {
            if (! preg_match(
                '/^(?P<name>\S+)\s+(?P<url>\S+)\s+\((?P<type>[^)]+)\)$/',
                $line,
                $matches
            )) {
                continue;
            }

            if (strtolower($matches['type']) !== 'push') {
                continue;
            }

            if (! preg_match($domainRegex, $matches['url'])) {
                continue;
            }

            if (false === strstr($matches['url'], $package)) {
                continue;
            }

            // FOUND!
            $discovered[] = $matches['name'];
        }

        if (0 === count($discovered)) {
            $this->reportNoRemoteFound($output, $provider, $package);
            return null;
        }

        if (1 === count($discovered)) {
            return array_pop($discovered);
        }

        return $this->promptForRemote($input, $output, $discovered);
    }

    /**
     * @return null|array<string> Array of lines as returned by
     *     `git remote -v`, or null if an error occurred.
     */
    private function fetchGitRemotes() : ?array
    {
        $command = 'git remote -v';
        exec($command, $output, $exitStatus);
        if ($exitStatus !== 0) {
            return null;
        }
        return $output;
    }

    private function getProviderDomain(Provider\ProviderInterface $provider) : string
    {
        if (! $provider instanceof Provider\ProviderNameProviderInterface) {
            throw Exception\InvalidProviderException::forIncompleteProvider($provider);
        }

        return $provider->getDomainName();
    }

    private function reportNoRemoteFound(
        OutputInterface $output,
        Provider\ProviderInterface $provider,
        string $package
    ) : void {
        $output->writeln('<error>Cannot determine remote to which to push tag!</error>');
        $output->writeln(sprintf(
            '- Do no remotes registered in your repository match the provider in use? ("%s")',
            $this->getProviderDomain($provider)
        ));
        $output->writeln(sprintf(
            '- Do no remotes registered in your repository match the <package> provided? ("%s")',
            $package
        ));
    }

    private function promptForRemote(InputInterface $input, OutputInterface $output, array $remotes) : ?string
    {
        $choices = array_merge($remotes, ['abort' => 'Abort release']);

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'More than one valid remote was found; which one should I use?',
            $choices
        );

        $remote = $helper->ask($input, $output, $question);

        if ('Abort release' === $remote) {
            $output->writeln('<error>Aborted at user request</error>');
            return null;
        }

        return $remote;
    }
}
