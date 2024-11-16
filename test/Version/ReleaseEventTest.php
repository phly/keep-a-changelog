<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;
    private ReleaseEvent $event;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->input->expects($this->any())->method('getArgument')->with('version')->willReturn('1.2.3');
        $this->input->expects($this->any())->method('getOption')->with('tag-name')->willReturn('v1.2.3');

        $this->event = new ReleaseEvent($this->input, $this->output, $this->dispatcher);
    }

    public function testPropagationIsNotStoppedInitially()
    {
        $this->assertFalse($this->event->isPropagationStopped());
    }

    public function testDispatcherIsAccessible()
    {
        $this->assertSame($this->dispatcher, $this->event->dispatcher());
    }

    public function testVersionIsAccessible()
    {
        $this->assertSame('1.2.3', $this->event->version());
    }

    public function testTagNameIsAccessible()
    {
        $this->assertSame('v1.2.3', $this->event->tagName());
    }

    public function testTagNameMatchesVersionWhenNoTagNameOptionPresentInInput()
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->any())->method('getArgument')->with('version')->willReturn('1.2.3');
        $input->expects($this->any())->method('getOption')->with('tag-name')->willReturn(null);

        $event = new ReleaseEvent($input, $this->output, $this->dispatcher);

        $this->assertSame('1.2.3', $event->tagName());
    }

    public function testEventHasNoConfigComposedInitially()
    {
        $this->assertNull($this->event->config());
        $this->assertTrue($this->event->missingConfiguration());
    }

    public function testMarkingConfigurationDiscoveredInjectsConfigButDoesNotMarkStopped()
    {
        $config = new Config();
        $this->event->discoveredConfiguration($config);

        $this->assertFalse($this->event->missingConfiguration());
        $this->assertFalse($this->event->isPropagationStopped());
        $this->assertSame($config, $this->event->config());
    }

    public function testMarkingConfigurationIncompleteStopsEvent()
    {
        $this->event->configurationIncomplete();
        $this->assertTrue($this->event->isPropagationStopped());
    }

    public function testEventHasNoChangelogComposedInitially(): ReleaseEvent
    {
        $this->assertNull($this->event->changelog());
        return $this->event;
    }

    /**
     * @depends testEventHasNoChangelogComposedInitially
     */
    public function testUpdatingChangelogPopulatesChangelog(ReleaseEvent $event)
    {
        $changelog = 'this is the changelog';
        $event->updateChangelog($changelog);
        $this->assertSame($changelog, $event->changelog());
    }

    public function testIndicatingChangelogFileIsUnreadableStopsPropagationWithError()
    {
        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('unreadable'));
        $this->assertNull($this->event->changelogFileIsUnreadable('changelog.txt'));
        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingErrorParsingChangelogStopsPropagationWithError()
    {
        $expected = 'this is an error message';
        $error    = new RuntimeException($expected);

        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount, $expected): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('parsing', $message),
                    2       => TestCase::assertStringContainsString($expected, $message),
                    default => true,
                };

                return true;
            }));

        $this->assertNull($this->event->errorParsingChangelog('changelog.txt', $error));

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingProviderIsCompleteStopsPropagationWithFailure()
    {
        $this->output->expects($this->exactly(8))->method('writeln')->with($this->isType('string'));

        $this->assertNull($this->event->providerIsIncomplete());

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingCouldNotFindTagStopsPropagationWithFailure()
    {
        $this->output->expects($this->exactly(1))->method('writeln')->with($this->isType('string'));

        $this->event->couldNotFindTag();

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingTaggingFailedStopsPropagationWithFailure()
    {
        $this->output->expects($this->exactly(2))->method('writeln')->with($this->isType('string'));

        $this->event->taggingFailed();

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingErrorCreatingReleaseStopsPropagationWithFailure()
    {
        $expected = 'this is an error message';
        $error    = new RuntimeException($expected);

        $invokedCount = $this->atLeast(3);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount, $expected): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('creating release', $message),
                    2       => TestCase::assertStringContainsString('error was caught', $message),
                    3       => TestCase::assertStringContainsString($expected, $message),
                    default => true,
                };

                return true;
            }));

        $this->assertNull($this->event->errorCreatingRelease($error));

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingUnexpectedProviderResultWhenCreatingReleaseStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('creating release', $message),
                    2       => TestCase::assertStringContainsString('API call', $message),
                    default => true,
                };

                return true;
            }));

        $this->assertNull($this->event->unexpectedProviderResult());

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testReleaseCreationErrorDueToAuthenticationProvidesUniqueMessage(): void
    {
        $e = new RuntimeException('this is the error message', 401);

        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Invalid credentials', $message),
                    2       => TestCase::assertStringContainsString('The credentials associated with your Git provider are invalid', $message),
                    default => true,
                };

                return true;
            }));

        $this->assertNull($this->event->errorCreatingRelease($e));
        $this->assertTrue($this->event->failed());
    }
}
