<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Prophecy\Object\ObjectProphecy;
use ReflectionMethod;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ExecuteCommandTrait
{
    /** @var InputInterface|ObjectProphecy */
    protected $input;

    /** @var OutputInterface|ObjectProphecy */
    protected $output;

    public function executeCommand(Command $command): int
    {
        $r = new ReflectionMethod($command, 'execute');
        $r->setAccessible(true);
        return $r->invoke($command, $this->input->reveal(), $this->output->reveal());
    }
}
