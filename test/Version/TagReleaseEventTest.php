<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagReleaseEventTest extends TestCase
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

        $this->config->expects($this->any())->method('package')->willReturn('some/package');
    }

    public function createEvent(string $version, string $tagName): TagReleaseEvent
    {
        $event = new TagReleaseEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            $version,
            $tagName
        );
        $event->discoveredConfiguration($this->config);
        return $event;
    }

    public function testImplementsPackageEvent(): TagReleaseEvent
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
        $this->assertSame($this->input, $event->input());
        $this->assertSame($this->output, $event->output());
        $this->assertSame($this->dispatcher, $event->dispatcher());
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

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Created tag "v1.2.3" for package "some/package"'));
        $this->output->expects($this->once())->method('write')->with($changelog);

        $event->updateChangelog($changelog);

        $this->assertNull($event->taggingComplete());
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testTagOperationFailedMarksEventFailed(): void
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Error creating tag', $message),
                    2       => TestCase::assertStringContainsString('"git tag" operation failed', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent('1.2.3', 'v1.2.3');

        $event->tagOperationFailed();

        $this->assertTrue($event->failed());
    }

    public function testUnversionedChangesPresentMarksEventFailed(): void
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('changes present', $message),
                    2       => TestCase::assertStringContainsString('check them in', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent('1.2.3', 'v1.2.3');

        $event->unversionedChangesPresent();

        $this->assertTrue($event->failed());
    }

    public function testChangelogMissingDateMarksEventFailed(): void
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('does not have a release date associated', $message),
                    2       => TestCase::assertStringContainsString('run version:ready', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent('1.2.3', 'v1.2.3');

        $event->changelogMissingDate();

        $this->assertTrue($event->failed());
    }
}
