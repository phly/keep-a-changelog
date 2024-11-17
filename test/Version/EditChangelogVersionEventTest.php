<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\EditChangelogVersionEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class EditChangelogVersionEventTest extends TestCase
{
    private Config&MockObject $config;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->config     = $this->createMock(Config::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->config->expects($this->any())->method('changelogFile')->willReturn('CHANGELOG.md');
    }

    public function createEvent(?string $version = null, ?string $editor = null): EditChangelogVersionEvent
    {
        return new EditChangelogVersionEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            $version,
            $editor
        );
    }

    public function testEventImplementsPackageEvent(): EditChangelogVersionEvent
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(EventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testEventImplementsChangelogAwareEvent(EditChangelogVersionEvent $event)
    {
        $this->assertInstanceOf(ChangelogEntryAwareEventInterface::class, $event);
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testEventImplementsEditorAwareEvent(EditChangelogVersionEvent $event)
    {
        $this->assertInstanceOf(EditorAwareEventInterface::class, $event);
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testPropagationIsNotStoppedByDefault(EditChangelogVersionEvent $event)
    {
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @depends testEventImplementsPackageEvent
     */
    public function testIsNotFailedByDefault(EditChangelogVersionEvent $event)
    {
        $this->assertFalse($event->failed());
    }

    public function testMarkingEditorFailedEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent();
        $event->discoveredConfiguration($this->config);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Could not edit CHANGELOG.md'));

        $this->assertNull($event->editorFailed());
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function versionExpectations(): iterable
    {
        yield 'latest' => [null, 'most recent changelog'];
        yield 'specific' => ['1.2.3', 'change for version 1.2.3'];
    }

    /**
     * @dataProvider versionExpectations
     */
    public function testMarkingEditCompleteEmitsOutputWithoutStoppingPropagationOrFailure(
        ?string $version,
        string $expectedPhrase
    ) {
        $event = $this->createEvent($version);
        $event->discoveredConfiguration($this->config);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains(sprintf('Edited %s in CHANGELOG.md', $expectedPhrase)));

        $this->assertNull($event->editComplete());
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }
}
