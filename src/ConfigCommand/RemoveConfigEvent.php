<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\Common\IOTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class RemoveConfigEvent implements
    IOInterface,
    StoppableEventInterface
{
    use IOTrait;

    /** @var bool */
    private $failed = false;

    /** @var bool */
    private $removeGlobal;

    /** @var bool */
    private $removeLocal;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        bool $removeLocal,
        bool $removeGlobal
    ) {
        $this->input        = $input;
        $this->output       = $output;
        $this->removeLocal  = $removeLocal;
        $this->removeGlobal = $removeGlobal;
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }

    public function failed(): bool
    {
        return $this->failed;
    }

    public function removeGlobal(): bool
    {
        return $this->removeGlobal;
    }

    public function removeLocal(): bool
    {
        return $this->removeLocal;
    }

    public function deletedConfigFile(string $configFile): void
    {
        $this->output->writeln(sprintf(
            '<info>Removed the file %s</info>',
            $configFile
        ));
    }

    public function abort(string $configFile): void
    {
        $this->output->writeln(sprintf(
            '<info>Aborted removal of %s at user request</info>',
            $configFile
        ));
    }

    public function configFileNotFound(string $configFile): void
    {
        $this->output->writeln(sprintf(
            '<error>Cannot remove config file %s; file does not exist</error>',
            $configFile
        ));
    }

    public function errorRemovingConfig(string $configFile): void
    {
        $this->failed = true;
        $this->output->writeln('<error>Operation failed</error>');
        $this->output->writeln(sprintf(
            'Unable to remove the file %s; check for valid permissions.',
            $configFile
        ));
        $this->output->writeln('You may need to remove the file manually.');
    }

    public function missingOptions(): void
    {
        $this->failed = true;
        $this->output->writeln('<error>Missing options!</error>');
        $this->output->writeln('One or more of the --local or --global options MUST be present.');
    }
}
