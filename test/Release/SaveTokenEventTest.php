<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\SaveTokenEvent;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveTokenEventTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class)->reveal();
        $this->output = $this->prophesize(OutputInterface::class)->reveal();
        $this->helper = $this->prophesize(QuestionHelper::class)->reveal();
        $this->event  = new SaveTokenEvent(
            $this->input,
            $this->output,
            $this->helper,
            'this-is-the-token'
        );
    }

    public function testEventIsStoppable()
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $this->event);
    }

    public function testAllConstructorArgumentsAreAccessible()
    {
        $this->assertSame($this->input, $this->event->input());
        $this->assertSame($this->output, $this->event->output());
        $this->assertSame($this->helper, $this->event->questionHelper());
        $this->assertSame('this-is-the-token', $this->event->token());
    }

    public function testAbortingStopsPropagation()
    {
        $this->event->abort();
        $this->assertTrue($this->event->isPropagationStopped());
    }
}
