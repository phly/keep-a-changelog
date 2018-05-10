<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Phly\KeepAChangelog\Provider\GetProviderTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ReleaseCommand extends Command
{
    use ConfigFileTrait;
    use GetChangelogFileTrait;
    use GetProviderTrait;

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
$HOME/.keep-a-changelog/token for the value unless one is provided via the --token option. 
When a token is provided via the --token option, the tool will prompt you to ask if you
wish to store the token in that location for later use.

When complete, the tool will provide a URL to the created release.

EOH;

    protected function configure() : void
    {
        $this->setDescription('Create a new release using the relevant changelog entry.');
        $this->setHelp(self::HELP);
        $this->addArgument(
            'package',
            InputArgument::REQUIRED,
            'Package to release; must be in org/repo format, and match the github repository name'
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
            'Git remote to push tag to; defaults to "origin"'
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
        $this->addOption(
            'provider',
            null,
            InputOption::VALUE_OPTIONAL,
            'Repository provider. Options: github or gitlab; defaults to github'
        );
        $this->addOption(
            'global',
            '-g',
            InputOption::VALUE_NONE,
            'Use the global config file'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $version = $input->getArgument('version');
        $tagName = $input->getOption('tagname') ?: $version;

        $this->verifyTagExists($tagName);

        $package = $input->getArgument('package');
        $token = $this->getToken($input, $output);
        if (! $token) {
            return 1;
        }

        $changelogFile = $this->getChangelogFile($input);
        if (! is_readable($changelogFile)) {
            throw Exception\ChangelogFileNotFoundException::at($changelogFile);
        }

        $output->writeln('<info>Preparing changelog for release</info>');

        $parser = new ChangelogParser();
        $changelog = $parser->findChangelogForVersion(
            file_get_contents($changelogFile),
            $version
        );

        $formatter = new ChangelogFormatter();
        $changelog = $formatter->format($changelog);

        $remote = $input->getOption('remote') ?? 'origin';
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

        $provider = $this->getProvider($input);
        $release = $provider->createRelease(
            $package,
            $releaseName,
            $tagName,
            $changelog,
            $token
        );
        if (! $release) {
            $output->writeln('<error>Error creating release!</error>');
            $output->writeln('Check the output logs for details');
            return 1;
        }

        if ($input->getOption('token')) {
            $this->promptToSaveToken($token, $input, $output);
        }

        $output->writeln(sprintf('<info>Created %s<info>', $release));

        return 0;
    }

    private function getToken(InputInterface $input, OutputInterface $output) : ?string
    {
        $token = $input->getOption('token');
        if ($token) {
            return trim($token);
        }

        $config = $this->getConfig($input);

        if (empty($config->token())) {
            $configFile = $this->getConfigFile($input);
            $output->writeln(sprintf(
                '<error>No token provided, and could not find it in the config file %s</error>',
                $configFile)
            );
            $output->writeln(sprintf(
                'Please provide the --token option, or create the file %s with your'
                . ' GitHub personal access token as the sole contents',
                $tokenFile
            ));
            return null;
        }

        return trim(file_get_contents($tokenFile));
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
        [$org, $repo] = explode('/', $package, 2);
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
}
