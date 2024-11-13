<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractRemoveConfigListener;
use Phly\KeepAChangelog\ConfigCommand\RemoveConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class AbstractRemoveConfigListenerTestCase extends TestCase
{
    protected InputInterface&MockObject $input;
    protected OutputInterface&MockObject $output;

    abstract public function getListener(): AbstractRemoveConfigListener;

    abstract public function getListenerWithFileNotFound(): AbstractRemoveConfigListener;

    abstract public function getListenerWithUnlinkableFile(): AbstractRemoveConfigListener;

    abstract public function configureEventToRemove(RemoveConfigEvent&MockObject $event): void;

    abstract public function configureEventToSkipRemove(RemoveConfigEvent&MockObject $event): void;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function getEvent(): RemoveConfigEvent&MockObject
    {
        /** @var RemoveConfigEvent&MockObject $event */
        $event = $this->createMock(RemoveConfigEvent::class);

        $event->expects($this->any())->method('input')->willReturn($this->input);
        $event->expects($this->any())->method('output')->willReturn($this->output);

        return $event;
    }

    public function testListenerReturnsEarlyIfEventNotConfiguredToRemove()
    {
        $listener = $this->getListener();
        $event    = $this->getEvent();

        $this->configureEventToSkipRemove($event);
        $event->expects($this->never())->method('configFileNotFound');
        $event->expects($this->never())->method('abort');
        $event->expects($this->never())->method('errorRemovingConfig');
        $event->expects($this->never())->method('deletedConfigFile');

        $this->assertNull($listener($event));
    }

    public function testListenerReturnsEarlyIfConfigFileNotFound()
    {
        $listener = $this->getListenerWithFileNotFound();
        $event    = $this->getEvent();

        $this->configureEventToRemove($event);
        $event->expects($this->once())->method('configFileNotFound');
        $event->expects($this->never())->method('abort');
        $event->expects($this->never())->method('errorRemovingConfig');
        $event->expects($this->never())->method('deletedConfigFile');

        $this->assertNull($listener($event));
    }

    public function testAllowsUserToAbortRemoval()
    {
        $event          = $this->getEvent();
        $listener       = $this->getListener();
        $questionHelper = $this->createMock(QuestionHelper::class);

        $questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->input,
                $this->output,
                $this->callback(function (ConfirmationQuestion $question): bool {
                    TestCase::assertMatchesRegularExpression('/delete this file/', $question->getQuestion());
                    return true;
                })
            )
            ->willReturn(false);

        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('Found the following configuration file'));

        $this->configureEventToRemove($event);
        $event->expects($this->never())->method('configFileNotFound');
        $event->expects($this->once())->method('abort')->with($this->isType('string'));
        $event->expects($this->never())->method('errorRemovingConfig');
        $event->expects($this->never())->method('deletedConfigFile');

        $listener->questionHelper = $questionHelper;

        $this->assertNull($listener($event));
    }

    public function testNotifiesOfRemovalError()
    {
        $listener       = $this->getListenerWithUnlinkableFile();
        $event          = $this->getEvent();
        $questionHelper = $this->createMock(QuestionHelper::class);

        $questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->input,
                $this->output,
                $this->callback(function (ConfirmationQuestion $question): bool {
                    TestCase::assertMatchesRegularExpression('/delete this file/', $question->getQuestion());
                    return true;
                })
            )
            ->willReturn(true);

        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('Found the following configuration file'));

        $this->configureEventToRemove($event);
        $event->expects($this->never())->method('configFileNotFound');
        $event->expects($this->never())->method('abort');
        $event->expects($this->once())->method('errorRemovingConfig')->with($this->isType('string'));
        $event->expects($this->never())->method('deletedConfigFile');

        $listener->questionHelper = $questionHelper;

        $this->assertNull($listener($event));
    }

    public function testNotifiesOfRemovalCompletion()
    {
        $listener       = $this->getListener();
        $event          = $this->getEvent();
        $questionHelper = $this->createMock(QuestionHelper::class);

        $questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->input,
                $this->output,
                $this->callback(function (ConfirmationQuestion $question): bool {
                    TestCase::assertMatchesRegularExpression('/delete this file/', $question->getQuestion());
                    return true;
                })
            )
            ->willReturn(true);

        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('Found the following configuration file'));

        $this->configureEventToRemove($event);
        $event->expects($this->never())->method('configFileNotFound');
        $event->expects($this->never())->method('abort');
        $event->expects($this->never())->method('errorRemovingConfig');
        $event->expects($this->once())->method('deletedConfigFile')->with($this->isType('string'));

        $listener->questionHelper = $questionHelper;

        $this->assertNull($listener($event));
    }
}
