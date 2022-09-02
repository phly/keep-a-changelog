<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListVersionsEvent extends AbstractEvent
{
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }
}
