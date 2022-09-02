<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoteNameDiscoveryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->helper = $this->prophesize(QuestionHelper::class)->reveal();
        $this->input  = $this->prophesize(InputInterface::class)->reveal();
        $this->output = $this->prophesize(OutputInterface::class);
        $this->config = new Config();
        $this->event  = new RemoteNameDiscovery(
            $this->input,
            $this->output->reveal(),
            $this->config,
            $this->helper
        );
    }

    public function testPropagationIsNotStoppedWithDefaultConfigInstance()
    {
        $this->assertFalse($this->event->isPropagationStopped());
    }

    public function testRemoteIsNotMarkedAsFoundWithDefaultConfigInstance()
    {
        $this->assertFalse($this->event->remoteWasFound());
    }

    public function testCanAccessConfig()
    {
        $this->assertSame($this->config, $this->event->config());
    }

    public function testCanAccessQuestionHelper()
    {
        $this->assertSame($this->helper, $this->event->questionHelper());
    }

    public function testRemotesAreEmptyByDefault()
    {
        $this->assertSame([], $this->event->remotes());
    }

    public function testSettingRemotesMutatesRemotesWithoutMarkingAsFound()
    {
        $this->event->setRemotes(['origin', 'upstream']);
        $this->assertSame(['origin', 'upstream'], $this->event->remotes());
        $this->assertFalse($this->event->isPropagationStopped());
        $this->assertFalse($this->event->remoteWasFound());
    }

    public function testReportingNoGitRemoteFoundStopsPropagationWithoutFindingRemote()
    {
        $this->output->writeln(Argument::containingString('Cannot determine git remote'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('match the provider'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('match the <package>'))->shouldBeCalled();

        $this->assertNull($this->event->reportNoMatchingGitRemoteFound('some.tld', 'some/package'));
        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertFalse($this->event->remoteWasFound());
    }

    public function testAbortingStopsPropagationWithoutFindingRemote()
    {
        $this->output->writeln(Argument::containingString('Aborted'));

        $this->assertNull($this->event->abort());
        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertFalse($this->event->remoteWasFound());
    }

    public function testReportingRemoteFoundInjectsRemoteInConfigStopsPropagationAndMarksFound()
    {
        $this->assertNull($this->event->foundRemote('upstream'));
        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->remoteWasFound());
        $this->assertSame('upstream', $this->config->remote());
    }
}
