<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Release\CreateReleaseEvent;
use Phly\KeepAChangelog\Release\CreateReleaseListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class CreateReleaseListenerTest extends TestCase
{
    public function setUp()
    {
        $this->output   = $this->prophesize(OutputInterface::class);
        $this->event    = $this->prophesize(CreateReleaseEvent::class);
        $this->provider = $this->prophesize(ProviderInterface::class);

        $this->event->releaseName()->willReturn('some/package 1.2.3');
        $this->event->provider()->will([$this->provider, 'reveal']);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->package()->willReturn('some/package');
        $this->event->version()->willReturn('1.2.3');
        $this->event->changelog()->willReturn('this is the changelog');
        $this->event->token()->willReturn('this-is-the-token');

        $this->output
            ->writeln(Argument::containingString('Creating release "some/package 1.2.3"'))
            ->shouldBeCalled();
    }

    public function testMarksReleaseErrorWhenProviderRaisesException()
    {
        $e = new RuntimeException();
        $this->provider
             ->createRelease(
                 'some/package',
                 'some/package 1.2.3',
                 '1.2.3',
                 'this is the changelog',
                 'this-is-the-token'
             )
             ->willThrow($e);
        $this->event->errorCreatingRelease($e)->shouldBeCalled();

        $listener = new CreateReleaseListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->unexpectedProviderResult()->shouldNotHaveBeenCalled();
        $this->event->releaseCreated(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReportsProviderProblemIfProviderDoesNotReturnValueAfterCreatingRelease()
    {
        $this->provider
             ->createRelease(
                 'some/package',
                 'some/package 1.2.3',
                 '1.2.3',
                 'this is the changelog',
                 'this-is-the-token'
             )
             ->willReturn(null);
        $this->event->unexpectedProviderResult()->shouldBeCalled();

        $listener = new CreateReleaseListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->errorCreatingRelease(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->releaseCreated(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testMarksReleaseCreatedOnSuccess()
    {
        $this->provider
             ->createRelease(
                 'some/package',
                 'some/package 1.2.3',
                 '1.2.3',
                 'this is the changelog',
                 'this-is-the-token'
             )
             ->willReturn('url-to-release');
        $this->event->releaseCreated('url-to-release')->shouldBeCalled();

        $listener = new CreateReleaseListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->errorCreatingRelease(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->unexpectedProviderResult()->shouldNotHaveBeenCalled();
    }
}
