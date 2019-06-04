<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class ShowConfigEvent extends AbstractEvent
{
    /** @var bool */
    private $finished = false;

    /** @var bool */
    private $showGlobal;

    /** @var bool */
    private $showLocal;

    /** @var bool */
    private $showMerged;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        bool $showLocal,
        bool $showGlobal
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->showLocal  = $showLocal;
        $this->showGlobal = $showGlobal;
        $this->showMerged = ($showLocal && $showGlobal) || ! ($showGlobal || $showLocal);
    }

    public function isPropagationStopped() : bool
    {
        return $this->finished || $this->failed;
    }

    public function showGlobal() : bool
    {
        return $this->showGlobal;
    }

    public function showLocal() : bool
    {
        return $this->showLocal;
    }

    public function showMerged() : bool
    {
        return $this->showMerged;
    }

    public function displayConfig(string $config, string $type, string $location) : void
    {
        $this->finished = true;
        $this->output->writeln(sprintf(
            '<info>Showing %s configuration (%s)</info>',
            $type,
            $location
        ));
        $this->output->writeln($config);
        $this->output->writeln('');
    }

    public function displayMergedConfig(string $config) : void
    {
        $this->finished = true;
        $this->output->writeln('<info>Showing merged configuration</info>');
        $this->output->writeln($config);
        $this->output->writeln('');
    }

    public function configIsNotReadable(string $configFile, string $type) : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Unable to read configuration</error>');
        $this->output->writeln(sprintf(
            'The %s configuration file "%s" either does not exist, or is not readable.',
            $type,
            $configFile
        ));
    }
}
