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
        $expectedStrings = [
            'Cannot determine git remote' => false,
            'match the provider'          => false,
            'match the <package>'         => false,
        ];
        $this->output
            ->expects($this->atLeast(3))
            ->method('writeln')
            ->with($this->callback(function (string $message) use (&$expectedStrings): bool {
                foreach (array_keys($expectedStrings) as $expectedString) {
                    if (strstr($message, $expectedString)) {
                        $expectedStrings[$expectedString] = true;
                        return true;
                    }
                }
                return true;
            }));

        $this->assertNull($this->event->reportNoMatchingGitRemoteFound('some.tld', 'some/package'));
        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertFalse($this->event->remoteWasFound());
        $this->assertAllExpectedOutputEmitted($expectedStrings);
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

    private function assertAllExpectedOutputEmitted(array $expectedStrings): void
    {
        $notFound = [];
        foreach ($expectedStrings as $string => $found) {
            if (! $found) {
                $notFound[] = $string;
            }
        }

        if (count($notFound) === 0) {
            return;
        }

        $this->fail(sprintf('One or more expected output strings were not emitted: %s', implode(', ', $notFound)));
    }
}
