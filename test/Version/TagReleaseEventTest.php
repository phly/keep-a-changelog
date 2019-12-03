<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagReleaseEventTest extends TestCase
{
    protected function setUp() : void
    {
        $this->config     = $this->prophesize(Config::class);
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->config->package()->willReturn('some/package');
        $this->output->write(Argument::type('string'))->willReturn(null);
        $this->output->writeln(Argument::type('string'))->willReturn(null);
    }

    public function createEvent(string $version, string $tagName) : TagReleaseEvent
    {
        $event = new TagReleaseEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal(),
            $version,
            $tagName
        );
        $event->discoveredConfiguration($this->config->reveal());
        return $event;
    }

    public function testImplementsPackageEvent() : TagReleaseEvent
    {
        $event = $this->createEvent('1.2.3', 'v1.2.3');
        $this->assertInstanceOf(TagReleaseEvent::class, $event);
        $this->assertInstanceOf(EventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testImplementsStoppableEvent(TagReleaseEvent $event)
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testImplementsChangelogAwareEvent(TagReleaseEvent $event)
    {
        $this->assertInstanceOf(ChangelogAwareEventInterface::class, $event);
    }

    public function testConstructorArgumentsAreAccessible()
    {
        // New test, as setUp is called for each test, creating different instances.
        $event = $this->createEvent('1.2.3', 'v1.2.3');
        $this->assertSame($this->input->reveal(), $event->input());
        $this->assertSame($this->output->reveal(), $event->output());
        $this->assertSame($this->dispatcher->reveal(), $event->dispatcher());
        $this->assertSame('1.2.3', $event->version());
        $this->assertSame('v1.2.3', $event->tagName());
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testPropagationIsNotStoppedByDefault(TagReleaseEvent $event)
    {
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testNotInFailureStateByDefault(TagReleaseEvent $event)
    {
        $this->assertFalse($event->failed());
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testProxiesToConfigForPackageName(TagReleaseEvent $event)
    {
        $this->assertSame('some/package', $event->package());
    }

    public function testMarkingTaggingCompleteEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $changelog = 'This is the changelog';
        $event     = $this->createEvent('1.2.3', 'v1.2.3');
        $event->updateChangelog($changelog);

        $this->assertNull($event->taggingComplete());

        $this->output
            ->writeln(Argument::containingString('Created tag "v1.2.3" for package "some/package"'))
            ->shouldHaveBeenCalled();
        $this->output
            ->write($changelog)
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testMarkingTaggingFailedEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent('1.2.3', 'v1.2.3');

        $this->assertNull($event->taggingFailed());

        $this->output
            ->writeln(Argument::containingString('Error creating tag!'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('Check the output logs'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
