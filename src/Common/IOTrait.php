<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
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
 *
 * Properties are marked protected to allow abstract classes to compose this trait.
 */
trait IOTrait
{
    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    public function input(): InputInterface
    {
        return $this->input;
    }

    public function output(): OutputInterface
    {
        return $this->output;
    }
}
