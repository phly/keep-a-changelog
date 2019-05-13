<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\CreateReleaseEvent;
use Phly\KeepAChangelog\Release\CreateReleaseNameListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class CreateReleaseNameListenerTest extends TestCase
{
    public function setUp()
    {
        $this->input = $this->prophesize(InputInterface::class);
        $this->event = $this->prophesize(CreateReleaseEvent::class);
        $this->event->input()->will([$this->input, 'reveal']);
    }

    public function testSetsReleaseNameFromInputOptionWhenPresent()
    {
        $this->input->getOption('name')->willReturn('some/package 1.2.3');
        $this->event->setReleaseName('some/package 1.2.3')->shouldBeCalled();

        $listener = new CreateReleaseNameListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->package()->shouldNotHaveBeenCalled();
        $this->event->version()->shouldNotHaveBeenCalled();
    }

    public function testSetsReleaseNameBasedOnPackageAndVersionWhenNoInputOptionPresent()
    {
        $this->input->getOption('name')->willReturn(null);
        $this->event->package()->willReturn('some/package');
        $this->event->version()->willReturn('1.2.3');
        $this->event->setReleaseName('package 1.2.3')->shouldBeCalled();

        $listener = new CreateReleaseNameListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
