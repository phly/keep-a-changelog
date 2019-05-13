<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\IsTokenOptionPresentListener;
use Phly\KeepAChangelog\Release\SaveTokenEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class IsTokenOptionPresentListenerTest extends TestCase
{
    public function setUp()
    {
        $this->input = $this->prophesize(InputInterface::class);
        $this->event = $this->prophesize(SaveTokenEvent::class);
        $this->event->input()->will([$this->input, 'reveal']);
    }

    public function testListenerDoesNothingIfTokenPresentInInput()
    {
        $this->input->getOption('token')->willReturn('this-is-a-token');

        $listener = new IsTokenOptionPresentListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->abort()->shouldNotHaveBeenCalled();
    }

    public function testListenerAbortsEventIfNoTokenPresentInInput()
    {
        $this->input->getOption('token')->willReturn(null);
        $this->event->abort()->shouldBeCalled();

        $listener = new IsTokenOptionPresentListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
