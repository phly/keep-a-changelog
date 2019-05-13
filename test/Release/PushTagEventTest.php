<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Release\PushTagEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushTagEventTest extends TestCase
{
    public function setUp()
    {
        $this->input    = $this->prophesize(InputInterface::class)->reveal();
        $this->output   = $this->prophesize(OutputInterface::class);
        $this->question = $this->prophesize(QuestionHelper::class)->reveal();
        $this->config   = $this->prophesize(Config::class)->reveal();
    }

    public function createEvent() : PushTagEvent
    {
        return new PushTagEvent(
            $this->input,
            $this->output->reveal(),
            $this->question,
            $this->config,
            '1.2.3'
        );
    }

    public function testEventIsStoppable()
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
    }

    public function testPropagationIsNotStoppedByDefault()
    {
        $event = $this->createEvent();
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testConfigIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame($this->config, $event->config());
    }

    public function testQuestionHelperIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame($this->question, $event->questionHelper());
    }

    public function testTagNameIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame('1.2.3', $event->tagName());
    }

    public function testEventIndicatesTagIsNotPushedByDefault()
    {
        $event = $this->createEvent();
        $this->assertFalse($event->wasPushed());
    }

    public function testIndicatingRemoteResolutionFailedStopsPropagationWithoutPushing()
    {
        $event = $this->createEvent();

        $this->assertNull($event->gitRemoteResolutionFailed());

        $this->output
            ->writeln(Argument::containingString('Cannot determine remote'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('verify the command works'))
            ->shouldHaveBeenCalled();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->wasPushed());
    }

    public function testRemotesAreEmptyByDefault()
    {
        $event = $this->createEvent();
        $this->assertSame([], $event->remotes());
    }

    public function testCanSetGitRemotes()
    {
        $event   = $this->createEvent();
        $remotes = ['origin', 'upstream'];

        $event->setRemotes($remotes);

        $this->assertSame($remotes, $event->remotes());
    }

    public function testRemoteIsEmptyByDefault()
    {
        $event = $this->createEvent();
        $this->assertNull($event->remote());
    }

    public function testCanSetGitRemote()
    {
        $event  = $this->createEvent();
        $remote = 'upstream';

        $event->setRemote($remote);

        $this->assertSame($remote, $event->remote());
    }

    public function testIndicatingInvalidProviderDetectedStopsPropagationWithoutPushing()
    {
        $event = $this->createEvent();

        $this->assertNull($event->invalidProviderDetected('invalid-provider'));

        $this->output
            ->writeln(Argument::containingString('Provider of type invalid-provider'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('Please use the --remote switch'))
            ->shouldHaveBeenCalled();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->wasPushed());
    }

    public function testReportingNoMatchingGitRemoteFoundStopsPropagationWithoutPushing()
    {
        $event = $this->createEvent();

        $this->assertNull($event->reportNoMatchingGitRemoteFound('some-domain.tld', 'some/package'));

        $this->output
            ->writeln(Argument::containingString('Cannot determine remote to which to push tag!'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('provider in use? ("some-domain.tld")'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('<package> provided? ("some/package")'))
            ->shouldHaveBeenCalled();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->wasPushed());
    }

    public function testAbortingReleaseStopsPropagationWithoutPushing()
    {
        $event = $this->createEvent();

        $this->assertNull($event->abortRelease());

        $this->output
            ->writeln(Argument::containingString('Aborted at user request'))
            ->shouldHaveBeenCalled();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->wasPushed());
    }

    public function testMarkingPushSucceededStopsPropagationAndPushes()
    {
        $event = $this->createEvent();

        $this->assertNull($event->pushSucceeded());

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->wasPushed());
    }

    public function testMarkingPushFailedStopsPropagationWithoutPushing()
    {
        $event = $this->createEvent();

        $this->assertNull($event->pushFailed());

        $this->output
            ->writeln(Argument::containingString('Error pushing tag to remote'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('Please check the output for details'))
            ->shouldHaveBeenCalled();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->wasPushed());
    }
}
