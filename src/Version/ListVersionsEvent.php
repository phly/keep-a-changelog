<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListVersionsEvent extends AbstractEvent
{
    public function __construct(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input  = $input;
        $this->output = $output;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }
}
