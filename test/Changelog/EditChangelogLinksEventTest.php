<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\EditChangelogLinksEvent;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditChangelogLinksEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function createEvent(): EditChangelogLinksEvent
    {
        return new EditChangelogLinksEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
        );
    }

    public function testIsAPackageEvent(): EditChangelogLinksEvent
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

        $this->output
            ->expects($this->atLeastOnce())
            ->method('writeln')
            ->with($this->stringContains('Completed editing links for file changelog.txt'));
        $this->assertNull($event->editComplete('changelog.txt'));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingEditFailedEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent();

        $invokedCount = $this->exactly(2);

        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function($value) use ($invokedCount) {
                if ($invokedCount->getInvocationCount() !== 1) {
                    return true;
                }

                TestCase::assertStringContainsString('Editing links for file changelog.txt failed', $value);
                return true;
            }));

        $this->assertNull($event->editFailed('changelog.txt'));
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
