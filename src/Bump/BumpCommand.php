<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Common\CreateMilestoneOptionsTrait;
use Phly\KeepAChangelog\Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * Bump a changelog version.
 *
 * Bumps a changelog to the next major, minor or patch version, based
 * on how the command is configured at initialization.
 */
class BumpCommand extends Command
{
    use CreateMilestoneOptionsTrait;

    public const BUMP_MAJOR      = 'major';
    public const BUMP_MINOR      = 'minor';
    public const BUMP_PATCH      = 'patch';
    public const BUMP_BUGFIX     = self::BUMP_PATCH;
    public const BUMP_UNRELEASED = 'Unreleased';

    private const DESC_TEMPLATE            = 'Create a new changelog entry for the next %s release.';
    private const DESC_TEMPLATE_BUGFIX     = 'Alias for bump:patch.';
    private const DESC_TEMPLATE_UNRELEASED = 'Create an Unreleased changelog entry.';

    private const HELP_TEMPLATE = <<<'EOH'
Add a new %1$s release entry to the changelog, based on the latest release.

Parses the CHANGELOG.md file to determine the latest release, and creates
a new entry representing the next %1$s release.

If --create-milestone or --create-milestone-with-name are provided, a milestone
will be created for the repository as well.

EOH;

    private const HELP_TEMPLATE_UNRELEASED = <<<'EOH'
Add an Unreleased entry to the top of the changelog.

EOH;

    /** @var string[] */
    private $bumpMethods = [
        self::BUMP_MAJOR      => 'bumpMajorVersion',
        self::BUMP_MINOR      => 'bumpMinorVersion',
        self::BUMP_PATCH      => 'bumpPatchVersion',
        self::BUMP_UNRELEASED => BumpChangelogVersionEvent::UNRELEASED,
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
        if (! isset($this->bumpMethods[$type])) {
            throw Exception\InvalidBumpTypeException::forType($type);
        }

        $this->type       = $type;
        $this->dispatcher = $dispatcher;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        switch (true) {
            case $this->type === 'bugfix':
                $this->setDescription(self::DESC_TEMPLATE_BUGFIX);
                break;
            case $this->type === self::BUMP_UNRELEASED:
                $this->setDescription(self::DESC_TEMPLATE_UNRELEASED);
                break;
            default:
                $this->setDescription(sprintf(
                    self::DESC_TEMPLATE,
                    $this->type
                ));
        }

        $this->setHelp(
            $this->type === self::BUMP_UNRELEASED
                ? self::HELP_TEMPLATE_UNRELEASED
                : sprintf(
                    self::HELP_TEMPLATE,
                    $this->type
                )
        );

        $this->injectMilestoneOptions($this);
    }

    /**
     * @throws Exception\ChangelogFileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $event = $this->dispatcher
            ->dispatch(new BumpChangelogVersionEvent(
                $input,
                $output,
                $this->dispatcher,
                $this->bumpMethods[$this->type]
            ));

        if ($event->failed()) {
            return 1;
        }

        if (! $this->isMilestoneCreationRequested($input)) {
            return 0;
        }

        return $this
            ->triggerCreateMilestoneEvent(
                $this->getMilestoneName($input, $event->version()),
                $output,
                $this->dispatcher
            )
            ->failed()
                ? 1
                : 0;
    }
}
