<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EditorProviderTrait;
use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\Common\IOTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditConfigEvent implements
    EditorAwareEventInterface,
    IOInterface,
    StoppableEventInterface
{
    use EditorProviderTrait;
    use IOTrait;
    
    /** @var bool */
    private $failed = false;

    /** @var bool */
    private $editGlobal;

    /** @var bool */
    private $editLocal;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        bool $editLocal,
        bool $editGlobal,
        ?string $editor = null
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->editLocal  = $editLocal;
        $this->editGlobal = $editGlobal;
        $this->editor     = $editor;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function editGlobal() : bool
    {
        return $this->editGlobal;
    }

    public function editLocal() : bool
    {
        return $this->editLocal;
    }

    public function failed() : bool
    {
        return $this->failed;
    }

    public function editComplete(string $configFile) : void
    {
        $this->output->writeln(sprintf(
            '<info>Completed editing %s',
            $configFile
        ));
    }

    public function configFileNotFound(string $configFile) : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Could not find config file %s</error>',
            $configFile
        ));
        $this->output->writeln('You may need to use the config:create command to create it first.');
    }

    public function editFailed(string $configFile) : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Editing config file %s failed</error>',
            $configFile
        ));
        $this->output->writeln('Review the output above for potential errosr.');
    }

    public function tooManyOptions() : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Too many options</error>');
        $this->output->writeln('You may only use ONE of --global or --local at a time.');
    }
}
