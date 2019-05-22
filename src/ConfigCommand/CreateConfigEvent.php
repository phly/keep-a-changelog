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

class CreateConfigEvent implements
    IOInterface,
    StoppableEventInterface
{
    /** @var null|string */
    private $customChangelog;

    /** @var bool */
    private $createGlobal;

    /** @var bool */
    private $createLocal;

    /** @var bool */
    private $failed = false;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        bool $createLocal,
        bool $createGlobal,
        ?string $customChangelog
    ) {
        $this->input           = $input;
        $this->output          = $output;
        $this->createLocal     = $createLocal;
        $this->createGlobal    = $createGlobal;
        $this->customChangelog = $customChangelog;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function failed() : bool
    {
        return $this->failed;
    }

    public function createGlobal() : bool
    {
        return $this->createGlobal;
    }

    public function createLocal() : bool
    {
        return $this->createLocal;
    }

    public function customChangelog() : ?string
    {
        return $this->customChangelog;
    }

    public function fileExists(string $filename) : void
    {
        $this->output->writeln(sprintf(
            '<info>Config file already exists at %s; skipping</info>',
            $filename
        ));
    }

    public function creationFailed(string $filename) : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf('<error>Failed creating config file %s</error>', $filename));
        $this->output->writeln('Verify you have permission to create the file, and try again.');
    }
}
