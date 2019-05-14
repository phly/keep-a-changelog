<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\EventDispatcher\EventDispatcher;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Release;
use Phly\KeepAChangelog\ReleaseCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionMethod;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseCommandTest extends TestCase
{
    public function setUp()
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->config     = $this->prophesize(Config::class);

        $provider = $this->prophesize(ProviderInterface::class)->reveal();
        $this->config->provider()->willReturn($provider);
    }

    public function executeCommand(ReleaseCommand $command) : int
    {
        $r = new ReflectionMethod($command, 'execute');
        $r->setAccessible(true);
        return $r->invoke($command, $this->input->reveal(), $this->output->reveal());
    }

    public function createCommand() : ReleaseCommand
    {
        $questionHelper = $this->prophesize(QuestionHelper::class)->reveal();
        $helperSet      = $this->prophesize(HelperSet::class);
        $helperSet->get('question')->willReturn($questionHelper);

        $command = new ReleaseCommand('release', $this->dispatcher->reveal());
        $command->setHelperSet($helperSet->reveal());

        return $command;
    }

    public function testInstantiationWithoutArgumentsComposesDefaultEventDispatcherInstance()
    {
        $command = new ReleaseCommand();
        $this->assertAttributeInstanceOf(EventDispatcher::class, 'dispatcher', $command);
    }

    public function testConstructorCanAcceptDispatcher()
    {
        $command = new ReleaseCommand('release', $this->dispatcher->reveal());
        $this->assertAttributeSame($this->dispatcher->reveal(), 'dispatcher', $command);
    }

    public function testExecutionFailsEarlyWhenRequirementsNotMet()
    {
        $mockValidator = $this->prophesize(Release\ValidateRequirementsEvent::class);
        $mockValidator->requirementsMet()->willReturn(false);

        $this->dispatcher
            ->dispatch(Argument::type(Release\ValidateRequirementsEvent::class))
            ->will(function () use ($mockValidator) {
                return $mockValidator->reveal();
            });

        $command = $this->createCommand();

        $this->assertSame(1, $this->executeCommand($command));

        $this->output->writeln(Argument::any())->shouldNotHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\PrepareChangelogEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\PushTagEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\CreateReleaseEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\SaveTokenEvent::class))
            ->shouldNotHaveBeenCalled();
    }

    public function testExecutionFailsEarlyWhenUnableToParseChangelog()
    {
        $mockValidator = $this->prophesize(Release\ValidateRequirementsEvent::class);
        $mockValidator->requirementsMet()->willReturn(true);
        $mockValidator->version()->willReturn('1.2.3');
        $mockValidator->tagName()->willReturn('v1.2.3');
        $mockValidator->config()->will([$this->config, 'reveal']);

        $this->dispatcher
            ->dispatch(Argument::type(Release\ValidateRequirementsEvent::class))
            ->will(function () use ($mockValidator) {
                return $mockValidator->reveal();
            });

        $mockParser = $this->prophesize(Release\PrepareChangelogEvent::class);
        $mockParser->changelogIsReady()->willReturn(false);

        $this->dispatcher
            ->dispatch(Argument::type(Release\PrepareChangelogEvent::class))
            ->will(function () use ($mockParser) {
                return $mockParser->reveal();
            });

        $command = $this->createCommand();

        $this->assertSame(1, $this->executeCommand($command));

        $this->output->writeln(Argument::containingString('Preparing changelog'))->shouldHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\PushTagEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\CreateReleaseEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\SaveTokenEvent::class))
            ->shouldNotHaveBeenCalled();
    }

    public function testExecutionFailsEarlyWhenUnableToPushTag()
    {
        $mockValidator = $this->prophesize(Release\ValidateRequirementsEvent::class);
        $mockValidator->requirementsMet()->willReturn(true);
        $mockValidator->version()->willReturn('1.2.3');
        $mockValidator->tagName()->willReturn('v1.2.3');
        $mockValidator->config()->will([$this->config, 'reveal']);

        $this->dispatcher
            ->dispatch(Argument::type(Release\ValidateRequirementsEvent::class))
            ->will(function () use ($mockValidator) {
                return $mockValidator->reveal();
            });

        $mockParser = $this->prophesize(Release\PrepareChangelogEvent::class);
        $mockParser->changelogIsReady()->willReturn(true);
        $mockParser->changelog()->willReturn('the-changelog');

        $this->dispatcher
            ->dispatch(Argument::type(Release\PrepareChangelogEvent::class))
            ->will(function () use ($mockParser) {
                return $mockParser->reveal();
            });

        $mockTagger = $this->prophesize(Release\PushTagEvent::class);
        $mockTagger->wasPushed()->willReturn(false);

        $this->dispatcher
            ->dispatch(Argument::type(Release\PushTagEvent::class))
            ->will(function () use ($mockTagger) {
                return $mockTagger->reveal();
            });

        $command = $this->createCommand();

        $this->assertSame(1, $this->executeCommand($command));

        $this->output->writeln(Argument::containingString('Preparing changelog'))->shouldHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\CreateReleaseEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\SaveTokenEvent::class))
            ->shouldNotHaveBeenCalled();
    }

    public function testExecutionFailsEarlyWhenUnableToCreateRelease()
    {
        $this->markTestIncomplete(
            'This test can be re-enabled once Config::provider()'
            . ' has been updated to return a ProviderInterface instance'
        );

        $mockValidator = $this->prophesize(Release\ValidateRequirementsEvent::class);
        $mockValidator->requirementsMet()->willReturn(true);
        $mockValidator->version()->willReturn('1.2.3');
        $mockValidator->tagName()->willReturn('v1.2.3');
        $mockValidator->config()->will([$this->config, 'reveal']);

        $this->dispatcher
            ->dispatch(Argument::type(Release\ValidateRequirementsEvent::class))
            ->will(function () use ($mockValidator) {
                return $mockValidator->reveal();
            });

        $mockParser = $this->prophesize(Release\PrepareChangelogEvent::class);
        $mockParser->changelogIsReady()->willReturn(true);
        $mockParser->changelog()->willReturn('the-changelog');

        $this->dispatcher
            ->dispatch(Argument::type(Release\PrepareChangelogEvent::class))
            ->will(function () use ($mockParser) {
                return $mockParser->reveal();
            });

        $mockTagger = $this->prophesize(Release\PushTagEvent::class);
        $mockTagger->wasPushed()->willReturn(true);

        $this->dispatcher
            ->dispatch(Argument::type(Release\PushTagEvent::class))
            ->will(function () use ($mockTagger) {
                return $mockTagger->reveal();
            });

        $mockReleaser = $this->prophesize(Release\CreateReleaseEvent::class);
        $mockReleaser->wasCreated()->willReturn(false);

        $this->dispatcher
            ->dispatch(Argument::type(Release\CreateReleaseEvent::class))
            ->will(function () use ($mockReleaser) {
                return $mockReleaser->reveal();
            });

        $command = $this->createCommand();

        $this->assertSame(1, $this->executeCommand($command));

        $this->output->writeln(Argument::containingString('Preparing changelog'))->shouldHaveBeenCalled();
        $this->dispatcher
            ->dispatch(Argument::type(Release\SaveTokenEvent::class))
            ->shouldNotHaveBeenCalled();
    }

    public function testExecutionTriggersAllEventsOnSuccess()
    {
        $this->markTestIncomplete(
            'This test can be re-enabled once Config::provider()'
            . ' has been updated to return a ProviderInterface instance'
        );

        $mockValidator = $this->prophesize(Release\ValidateRequirementsEvent::class);
        $mockValidator->requirementsMet()->willReturn(true);
        $mockValidator->version()->willReturn('1.2.3');
        $mockValidator->tagName()->willReturn('v1.2.3');
        $mockValidator->config()->will([$this->config, 'reveal']);

        $this->dispatcher
            ->dispatch(Argument::type(Release\ValidateRequirementsEvent::class))
            ->will(function () use ($mockValidator) {
                return $mockValidator->reveal();
            });

        $mockParser = $this->prophesize(Release\PrepareChangelogEvent::class);
        $mockParser->changelogIsReady()->willReturn(true);
        $mockParser->changelog()->willReturn('the-changelog');

        $this->dispatcher
            ->dispatch(Argument::type(Release\PrepareChangelogEvent::class))
            ->will(function () use ($mockParser) {
                return $mockParser->reveal();
            });

        $mockTagger = $this->prophesize(Release\PushTagEvent::class);
        $mockTagger->wasPushed()->willReturn(true);

        $this->dispatcher
            ->dispatch(Argument::type(Release\PushTagEvent::class))
            ->will(function () use ($mockTagger) {
                return $mockTagger->reveal();
            });

        $mockReleaser = $this->prophesize(Release\CreateReleaseEvent::class);
        $mockReleaser->wasCreated()->willReturn(false);

        $this->dispatcher
            ->dispatch(Argument::type(Release\CreateReleaseEvent::class))
            ->will(function () use ($mockReleaser) {
                return $mockReleaser->reveal();
            });

        $this->dispatcher
            ->dispatch(Argument::type(Release\SaveTokenEvent::class))
            ->will(function ($args) {
                return $args[0];
            });

        $command = $this->createCommand();

        $this->assertSame(0, $this->executeCommand($command));

        $this->output->writeln(Argument::containingString('Preparing changelog'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('Created'))->shouldHaveBeenCalled();
    }
}
