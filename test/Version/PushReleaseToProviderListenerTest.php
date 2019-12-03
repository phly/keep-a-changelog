<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Version\PushReleaseToProviderListener;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class PushReleaseToProviderListenerTest extends TestCase
{
    protected function setUp() : void
    {
        $this->output   = $this->prophesize(OutputInterface::class);
        $this->event    = $this->prophesize(ReleaseEvent::class);
        $this->provider = $this->prophesize(ProviderInterface::class);

        $this->event->releaseName()->willReturn('some/package 1.2.3');
        $this->event->provider()->will([$this->provider, 'reveal']);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->version()->willReturn('1.2.3');
        $this->event->changelog()->willReturn('this is the changelog');

        $this->output
            ->writeln(Argument::containingString('Creating release "some/package 1.2.3"'))
            ->shouldBeCalled();
    }

    public function testMarksReleaseErrorWhenProviderRaisesException()
    {
        $e = new RuntimeException();
        $this->provider
            ->createRelease(
                'some/package 1.2.3',
                '1.2.3',
                'this is the changelog'
            )
            ->willThrow($e);
        $this->event->errorCreatingRelease($e)->shouldBeCalled();
        $this->event->tagName()->willReturn('1.2.3')->shouldBeCalled();

        $listener = new PushReleaseToProviderListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->unexpectedProviderResult()->shouldNotHaveBeenCalled();
        $this->event->releaseCreated(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReportsProviderProblemIfProviderDoesNotReturnValueAfterCreatingRelease()
    {
        $this->provider
            ->createRelease(
                'some/package 1.2.3',
                '1.2.3',
                'this is the changelog'
            )
            ->willReturn(null);
        $this->event->unexpectedProviderResult()->shouldBeCalled();
        $this->event->tagName()->willReturn('1.2.3')->shouldBeCalled();

        $listener = new PushReleaseToProviderListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->errorCreatingRelease(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->releaseCreated(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testMarksReleaseCreatedOnSuccess()
    {
        $this->provider
            ->createRelease(
                'some/package 1.2.3',
                '1.2.3',
                'this is the changelog'
            )
            ->willReturn('url-to-release');
        $this->event->releaseCreated('url-to-release')->shouldBeCalled();
        $this->event->tagName()->willReturn('1.2.3')->shouldBeCalled();

        $listener = new PushReleaseToProviderListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->errorCreatingRelease(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->unexpectedProviderResult()->shouldNotHaveBeenCalled();
    }
}
