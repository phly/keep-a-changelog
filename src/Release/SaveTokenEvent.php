<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\IOTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveTokenEvent implements StoppableEventInterface
{
    use IOTrait;

    /** @var bool */
    private $aborted = false;

    /** @var QuestionHelper */
    private $questionHelper;

    /** @var string */
    private $token;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        string $token
    ) {
        $this->input          = $input;
        $this->output         = $output;
        $this->questionHelper = $questionHelper;
        $this->token          = $token;
    }

    public function isPropagationStopped() : bool
    {
        return $this->aborted;
    }

    public function questionHelper() : QuestionHelper
    {
        return $this->questionHelper;
    }

    public function token() : string
    {
        return $this->token;
    }

    public function abort() : void
    {
        $this->aborted = true;
    }
}
