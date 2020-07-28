<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractRemoveConfigListener;
use Phly\KeepAChangelog\ConfigCommand\RemoveConfigEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class AbstractRemoveConfigListenerTestCase extends TestCase
{
    abstract public function getListener(): AbstractRemoveConfigListener;

    abstract public function getListenerWithFileNotFound(): AbstractRemoveConfigListener;

    abstract public function getListenerWithUnlinkableFile(): AbstractRemoveConfigListener;

    abstract public function configureEventToRemove(ObjectProphecy $event): void;

    abstract public function configureEventToSkipRemove(ObjectProphecy $event): void;

    protected function setUp(): void
    {
        $this->voidReturn = function () {
        };
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
    }

    public function getEventProphecy(): ObjectProphecy
    {
        $event = $this->prophesize(RemoveConfigEvent::class);

        $event->input()->will([$this->input, 'reveal']);
        $event->output()->will([$this->output, 'reveal']);
        $event->configFileNotFound(Argument::any())->will($this->voidReturn);
        $event->abort(Argument::any())->will($this->voidReturn);
        $event->errorRemovingConfig(Argument::any())->will($this->voidReturn);
        $event->deletedConfigFile(Argument::any())->will($this->voidReturn);

        return $event;
    }

    public function testListenerReturnsEarlyIfEventNotConfiguredToRemove()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToSkipRemove($event);

        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldNotHaveBeenCalled();
        $event->abort(Argument::any())->shouldNotHaveBeenCalled();
        $event->errorRemovingConfig(Argument::any())->shouldNotHaveBeenCalled();
        $event->deletedConfigFile(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerReturnsEarlyIfConfigFileNotFound()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToRemove($event);

        $listener = $this->getListenerWithFileNotFound();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldHaveBeenCalled();
        $event->abort(Argument::any())->shouldNotHaveBeenCalled();
        $event->errorRemovingConfig(Argument::any())->shouldNotHaveBeenCalled();
        $event->deletedConfigFile(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testAllowsUserToAbortRemoval()
    {
        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$this->input, 'reveal']),
                Argument::that([$this->output, 'reveal']),
                Argument::that(function ($question) {
                    TestCase::assertInstanceOf(ConfirmationQuestion::class, $question);
                    TestCase::assertRegExp('/delete this file/', $question->getQuestion());
                    return $question;
                })
            )
            ->willReturn(false);

        $this->output
            ->writeln(Argument::containingString('Found the following configuration file'))
            ->shouldBeCalled();

        $event = $this->getEventProphecy();
        $this->configureEventToRemove($event);

        $listener                 = $this->getListener();
        $listener->questionHelper = $questionHelper->reveal();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldNotHaveBeenCalled();
        $event->abort(Argument::type('string'))->shouldHaveBeenCalled();
        $event->errorRemovingConfig(Argument::any())->shouldNotHaveBeenCalled();
        $event->deletedConfigFile(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifiesOfRemovalError()
    {
        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$this->input, 'reveal']),
                Argument::that([$this->output, 'reveal']),
                Argument::that(function ($question) {
                    TestCase::assertInstanceOf(ConfirmationQuestion::class, $question);
                    TestCase::assertRegExp('/delete this file/', $question->getQuestion());
                    return $question;
                })
            )
            ->willReturn(true);

        $this->output
            ->writeln(Argument::containingString('Found the following configuration file'))
            ->shouldBeCalled();

        $event = $this->getEventProphecy();
        $this->configureEventToRemove($event);

        $listener                 = $this->getListenerWithUnlinkableFile();
        $listener->questionHelper = $questionHelper->reveal();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldNotHaveBeenCalled();
        $event->abort(Argument::any())->shouldNotHaveBeenCalled();
        $event->errorRemovingConfig(Argument::type('string'))->shouldHaveBeenCalled();
        $event->deletedConfigFile(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifiesOfRemovalCompletion()
    {
        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$this->input, 'reveal']),
                Argument::that([$this->output, 'reveal']),
                Argument::that(function ($question) {
                    TestCase::assertInstanceOf(ConfirmationQuestion::class, $question);
                    TestCase::assertRegExp('/delete this file/', $question->getQuestion());
                    return $question;
                })
            )
            ->willReturn(true);

        $this->output
            ->writeln(Argument::containingString('Found the following configuration file'))
            ->shouldBeCalled();

        $event = $this->getEventProphecy();
        $this->configureEventToRemove($event);

        $listener                 = $this->getListener();
        $listener->questionHelper = $questionHelper->reveal();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldNotHaveBeenCalled();
        $event->abort(Argument::any())->shouldNotHaveBeenCalled();
        $event->errorRemovingConfig(Argument::any())->shouldNotHaveBeenCalled();
        $event->deletedConfigFile(Argument::type('string'))->shouldHaveBeenCalled();
    }
}
