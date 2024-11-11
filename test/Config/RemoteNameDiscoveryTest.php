<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\RemoteNameDiscovery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoteNameDiscoveryTest extends TestCase
{
    private QuestionHelper&MockObject $helper;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private Config $config;
    private RemoteNameDiscovery $event;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(QuestionHelper::class);
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->config = new Config();
        $this->event  = new RemoteNameDiscovery(
            $this->input,
            $this->output,
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
        $invokedCounter = $this->exactly(3);
        $this->output
            ->expects($invokedCounter)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCounter): bool {
                match ($invokedCounter->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString('Cannot determine git remote', $message),
                    2 => TestCase::assertStringContainsString('match the provider', $message),
                    3 => TestCase::assertStringContainsString('match the <package>', $message),
                };
                return true;
            }));

        $this->assertNull($this->event->reportNoMatchingGitRemoteFound('some.tld', 'some/package'));
        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertFalse($this->event->remoteWasFound());
    }

    public function testAbortingStopsPropagationWithoutFindingRemote()
    {
        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('Aborted'));

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
