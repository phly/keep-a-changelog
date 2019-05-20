<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Phly\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * Add a new changelog entry using the version specified.
 */
class BumpToVersionCommand extends Command
{
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'Create a new changelog entry for the specified release version.';

    private const HELP = <<<'EOH'
Add a new release entry to the changelog, based on the latest release specified.

EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(
        ?string $name = null,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?: new EventDispatcher(new ListenerProvider());
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'Version to use with newly created changelog entry.'
        );
    }

    /**
     * @throws Exception\ChangelogFileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
            ->dispatch(new Bump\BumpChangelogVersionEvent(
                $input,
                $output,
                $this->dispatcher,
                null,
                $input->getArgument('version')
            ))
            ->failed()
            ? 1
            : 0;
    }
}
