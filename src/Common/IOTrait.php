<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Classes composing this trait are responsible for populating the input and
 * output properties during instantiation.
 *
 * Provides an implementation of IOInterface.
 */
trait IOTrait
{
    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    public function input() : InputInterface
    {
        return $this->input;
    }

    public function output() : OutputInterface
    {
        return $this->output;
    }
}
