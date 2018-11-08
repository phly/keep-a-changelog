<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Phly\KeepAChangelog\Provider\ProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntryCommand extends Command
{
    use GetChangelogFileTrait;
    use GetConfigValuesTrait;

    private const DESC_TEMPLATE = 'Create a new changelog entry for the latest changelog in the "%s" section';

    private const HELP_TEMPLATE = <<< 'EOH'
In the latest changelog entry, add the given entry in the section marked
"%s".

If the first entry in that section matches "- Nothing", that line will
be replaced with the new entry.

When the --pr option is provided, the entry will be prepended with a link
to the given pull request. If no --package option is present, we will
attempt to determine the package name from the composer.json file.
EOH;

    /** @var string */
    private $type;

    public function __construct(string $name)
    {
        if (false === strpos($name, ':')) {
            throw Exception\InvalidNoteTypeException::forCommandName($name);
        }

        [$initial, $type] = explode(':', $name, 2);
        if (! in_array($type, AddEntry::TYPES, true)) {
            throw Exception\InvalidNoteTypeException::forCommandName($name);
        }

        $this->type = $type;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription(sprintf(
            self::DESC_TEMPLATE,
            ucwords($this->type)
        ));
        $this->setHelp(sprintf(
            self::HELP_TEMPLATE,
            ucwords($this->type)
        ));
        $this->addArgument(
            'entry',
            InputArgument::REQUIRED,
            'Entry to add to the changelog'
        );
        $this->addOption(
            'pr',
            null,
            InputOption::VALUE_REQUIRED,
            'Pull request number to associate with entry'
        );
        $this->addOption(
            'package',
            null,
            InputOption::VALUE_REQUIRED,
            'Name of package in organization/repo format (for building link to a pull request)'
        );
        $this->addOption(
            'provider',
            null,
            InputOption::VALUE_OPTIONAL,
            'Repository provider. Options: github or gitlab; defaults to github'
        );
        $this->addOption(
            'global',
            'g',
            InputOption::VALUE_NONE,
            'Use the global config file'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln(sprintf(
            '<info>Preparing entry for %s section</info>',
            ucwords($this->type)
        ));

        $entry = $this->prepareEntry($input);
        $changelog = $this->getChangelogFile($input);

        $output->writeln(sprintf(
            '<info>Writing "%s" entry to %s</info>',
            ucwords($this->type),
            $changelog
        ));

        (new AddEntry())(
            $this->type,
            $changelog,
            $entry
        );

        return 0;
    }

    private function prepareEntry(InputInterface $input) : string
    {
        $entry = $input->getArgument('entry');
        if (empty($entry)) {
            throw Exception\EmptyEntryException::create();
        }

        $pr = $input->getOption('pr');
        if (! $pr) {
            return $entry;
        }

        if (! preg_match('/^[1-9]\d*$/', (string) $pr)) {
            throw Exception\InvalidPullRequestException::for($pr);
        }

        $config = $this->prepareConfig($input);

        return sprintf(
            '[#%d](%s) %s',
            (int) $pr,
            $this->preparePullRequestLink(
                (int) $pr,
                $input->getOption('package'),
                $this->getProvider($config)
            ),
            $entry
        );
    }

    private function preparePullRequestLink(int $pr, ?string $package, ProviderInterface $provider) : string
    {
        if (null !== $package) {
            $link = $this->generatePullRequestLink($pr, $package, $provider);

            if (null === $link) {
                throw Exception\InvalidPullRequestLinkException::forPackage($package, $pr);
            }

            return $link;
        }

        $link = $this->generatePullRequestLink($pr, (new ComposerPackage())->getName(realpath(getcwd())), $provider);

        if (null !== $link) {
            return $link;
        }

        foreach ($this->getPackageNames($provider) as $package) {
            $link = $this->generatePullRequestLink($pr, $package, $provider);

            if (null !== $link) {
                return $link;
            }
        }

        throw Exception\InvalidPullRequestLinkException::noValidLinks($pr);
    }

    private function getPackageNames(ProviderInterface $provider) : array
    {
        exec('git remote', $remotes, $return);

        if (0 !== $return) {
            return [];
        }

        $packages = [];

        foreach ($remotes as $remote) {
            $url = [];
            exec(sprintf('git remote get-url %s', escapeshellarg($remote)), $url, $return);

            if (0 !== $return) {
                continue;
            }

            if (0 === preg_match($provider->getRepositoryUrlRegex(), $url[0], $matches)) {
                continue;
            }

            $packages[] = $matches[1];
        }

        return $packages;
    }

    private function generatePullRequestLink(int $pr, string $package, ProviderInterface $provider) : ?string
    {
        if (! preg_match('#^[a-z0-9]+[a-z0-9_-]*/[a-z0-9]+[a-z0-9_-]*$#i', $package)) {
            throw Exception\InvalidPackageNameException::forPackage($package);
        }

        $link = $provider->generatePullRequestLink($package, $pr);

        return $this->probeLink($link) ? $link : null;
    }

    private function probeLink(string $link) : bool
    {
        $headers = get_headers($link, 1, stream_context_create(['http' => ['method' => 'HEAD']]));
        $statusLine = explode(' ', $headers[0]);
        $statusCode = (int) $statusLine[1];

        if ($statusCode < 300) {
            return true;
        }

        if ($statusCode >= 300 && $statusCode <= 399 && array_key_exists('Location', $headers)) {
            return $this->probeLink($headers['Location']);
        }

        return false;
    }
}
