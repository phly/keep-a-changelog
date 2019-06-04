<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_keys;
use function in_array;
use function sprintf;

/**
 * Bump a changelog version.
 *
 * Bumps a changelog to the next bugfix, minor, or major version, based
 * on how the command is configured at initialization.
 */
class BumpCommand extends Command
{
    public const BUMP_BUGFIX = 'bugfix';
    public const BUMP_MAJOR  = 'major';
    public const BUMP_MINOR  = 'minor';
    public const BUMP_PATCH  = self::BUMP_BUGFIX;

    private const DESC_TEMPLATE = 'Create a new changelog entry for the next %s release.';

    private const HELP_TEMPLATE = <<<'EOH'
Add a new %1$s release entry to the changelog, based on the latest release.

Parses the CHANGELOG.md file to determine the latest release, and creates
a new entry representing the next %1$s release.

EOH;

    /** @var string[] */
    private $bumpMethods = [
        self::BUMP_BUGFIX => 'bumpBugfixVersion',
        self::BUMP_MAJOR  => 'bumpMajorVersion',
        self::BUMP_MINOR  => 'bumpMinorVersion',
    ];

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var string */
    private $type;

    /**
     * @throws Exception\InvalidBumpTypeException
     */
    public function __construct(
        string $type,
        EventDispatcherInterface $dispatcher,
        ?string $name = null
    ) {
        if (! in_array($type, array_keys($this->bumpMethods), true)) {
            throw Exception\InvalidBumpTypeException::forType($type);
        }

        $this->type       = $type;
        $this->dispatcher = $dispatcher;
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

    /**
     * @throws Exception\ChangelogFileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
                ->dispatch(new BumpChangelogVersionEvent(
                    $input,
                    $output,
                    $this->dispatcher,
                    $this->bumpMethods[$this->type]
                ))
                ->failed()
                    ? 1
                    : 0;
    }
}
