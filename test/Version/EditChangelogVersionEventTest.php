<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class EditChangelogVersionEventTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->config     = $this->prophesize(Config::class);
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->config->changelogFile()->willReturn('CHANGELOG.md');
        $this->output->writeln(Argument::type('string'))->willReturn(null);
    }

    public function createEvent(?string $version = null, ?string $editor = null): EditChangelogVersionEvent
    {
        return new EditChangelogVersionEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal(),
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
        $event->discoveredConfiguration($this->config->reveal());

        $this->assertNull($event->editorFailed());
        $this->output
            ->writeln(Argument::containingString('Could not edit CHANGELOG.md'))
            ->shouldHaveBeenCalled();
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
        $event->discoveredConfiguration($this->config->reveal());

        $this->assertNull($event->editComplete());
        $this->output
            ->writeln(Argument::containingString(sprintf('Edited %s in CHANGELOG.md', $expectedPhrase)))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }
}
