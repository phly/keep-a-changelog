<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\EditChangelogLinksEvent;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditChangelogLinksEventTest extends TestCase
{
    protected function setUp() : void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->output->writeln(Argument::any())->willReturn(null);
    }

    public function createEvent() : EditChangelogLinksEvent
    {
        return new EditChangelogLinksEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal()
        );
    }

    public function testIsAPackageEvent() : EditChangelogLinksEvent
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(EventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testIsAPackageEvent
     */
    public function testIsAnEditorAwareEvent(EditChangelogLinksEvent $event)
    {
        $this->assertInstanceOf(EditorAwareEventInterface::class, $event);
    }

    /**
     * @depends testIsAPackageEvent
     */
    public function testPropagationIsNotStoppedByDefault(EditChangelogLinksEvent $event)
    {
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @depends testIsAPackageEvent
     */
    public function testIsNotInFailureStateByDefault(EditChangelogLinksEvent $event)
    {
        $this->assertFalse($event->failed());
    }

    /**
     * @depends testIsAPackageEvent
     */
    public function testDoesNotAppendLinksByDefault(EditChangelogLinksEvent $event)
    {
        $this->assertFalse($event->appendLinksToChangelogFile());
    }

    /**
     * @depends testIsAPackageEvent
     */
    public function testDoesNotComposeLinksByDefault(EditChangelogLinksEvent $event)
    {
        $this->assertNull($event->links());
    }

    public function testDiscoveringLinksMakesThemAccessible()
    {
        $links = new ChangelogEntry();
        $event = $this->createEvent();

        $this->assertNull($event->discoveredLinks($links));
        $this->assertSame($links, $event->links());
    }

    public function testNotifyingNoLinksDiscoveredTogglesAppendFlag()
    {
        $event = $this->createEvent();

        $this->assertNull($event->noLinksDiscovered());
        $this->assertTrue($event->appendLinksToChangelogFile());
    }

    public function testNotifyingEditCompleteEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent();

        $this->assertNull($event->editComplete('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Completed editing links for file changelog.txt'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingEditFailedEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent();

        $this->assertNull($event->editFailed('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Editing links for file changelog.txt failed'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
