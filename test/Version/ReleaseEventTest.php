<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseEventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class)->reveal();

        $this->input->getArgument('version')->willReturn('1.2.3');
        $this->input->getOption('tag-name')->willReturn('v1.2.3');

        $this->event = new ReleaseEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher
        );
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
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('version')->willReturn('1.2.3');
        $input->getOption('tag-name')->willReturn(null);

        $event = new ReleaseEvent(
            $input->reveal(),
            $this->output->reveal(),
            $this->dispatcher
        );

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
        $this->output->writeln(Argument::containingString('unreadable'))->shouldBeCalled();
        $this->assertNull($this->event->changelogFileIsUnreadable('changelog.txt'));
        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingErrorParsingChangelogStopsPropagationWithError()
    {
        $message = 'this is an error message';
        $error   = new RuntimeException($message);
        $this->output->writeln(Argument::containingString('parsing'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString($message))->shouldBeCalled();

        $this->assertNull($this->event->errorParsingChangelog('changelog.txt', $error));

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingProviderIsCompleteStopsPropagationWithFailure()
    {
        $this->output->writeln(Argument::any())->shouldBeCalledTimes(8);

        $this->assertNull($this->event->providerIsIncomplete());

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingCouldNotFindTagStopsPropagationWithFailure()
    {
        $this->output->writeln(Argument::any())->shouldBeCalledTimes(1);

        $this->event->couldNotFindTag();

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingTaggingFailedStopsPropagationWithFailure()
    {
        $this->output->writeln(Argument::any())->shouldBeCalledTimes(2);

        $this->event->taggingFailed();

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingErrorCreatingReleaseStopsPropagationWithFailure()
    {
        $message = 'this is an error message';
        $error   = new RuntimeException($message);
        $this->output->writeln(Argument::containingString('creating release'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('error was caught'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString($message))->shouldBeCalled();

        $this->assertNull($this->event->errorCreatingRelease($error));

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }

    public function testIndicatingUnexpectedProviderResultWhenCreatingReleaseStopsPropagationWithFailure()
    {
        $this->output->writeln(Argument::containingString('creating release'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('API call'))->shouldBeCalled();

        $this->assertNull($this->event->unexpectedProviderResult());

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->failed());
    }
}
