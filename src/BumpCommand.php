<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Bump a changelog version.
 *
 * Bumps a changelog to the next bugfix, minor, or major version, based
 * on how the command is configured at initialization.
 */
class BumpCommand extends Command
{
    use GetChangelogFileTrait;

    public const BUMP_BUGFIX = 'bugfix';
    public const BUMP_MAJOR = 'major';
    public const BUMP_MINOR = 'minor';
    public const BUMP_PATCH = self::BUMP_BUGFIX;

    private const DESC_TEMPLATE = 'Create a new changelog entry for the next %s release.';

    private const HELP_TEMPLATE = <<< 'EOH'
Add a new %1$s release entry to the changelog, based on the latest release.

Parses the CHANGELOG.md file to determine the latest release, and creates
a new entry representing the next %1$s release.

EOH;

    /** @var string[] */
    private $bumpMethods = [
        self::BUMP_BUGFIX => 'bumpBugfixVersion',
        self::BUMP_MAJOR => 'bumpMajorVersion',
        self::BUMP_MINOR => 'bumpMinorVersion',
    ];

    /** @var string */
    private $type;

    public function __construct(string $type, string $name = null)
    {
        if (! in_array($type, array_keys($this->bumpMethods), true)) {
            throw Exception\InvalidBumpTypeException::forType($type);
        }

        $this->type = $type;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription(sprintf(
            self::DESC_TEMPLATE,
            $this->type
        ));


        $this->setHelp(sprintf(
            self::HELP_TEMPLATE,
            $this->type
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $changelogFile = $this->getChangelogFile($input);
        if (! is_readable($changelogFile)) {
            throw Exception\ChangelogFileNotFoundException::at($changelogFile);
        }

        $method = $this->bumpMethods[$this->type];

        $bumper = new ChangelogBump($changelogFile);
        $latest = $bumper->findLatestVersion();
        $version = $bumper->$method($latest);
        $bumper->updateChangelog($version);

        $output->writeln(sprintf(
            '<info>Bumped changelog version to %s</info>',
            $version
        ));

        return 0;
    }
}
