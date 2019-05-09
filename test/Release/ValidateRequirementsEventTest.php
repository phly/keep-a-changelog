<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Release\ValidateRequirementsEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateRequirementsEventTest extends TestCase
{
    public function setUp()
    {
        $this->input = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
    }

    public function createEvent() : ValidateRequirementsEvent
    {
        $this->input->getArgument('version')->willReturn('1.2.3');
        $this->input->getOption('tagname')->willReturn(null);
        return new ValidateRequirementsEvent($this->input->reveal(), $this->output->reveal());
    }

    public function testConstructorSetsVersionFromInputArgument() : array
    {
        $version = '1.2.3';
        $this->input->getArgument('version')->willReturn($version);
        $this->input->getOption('tagname')->willReturn(null);

        $event = new ValidateRequirementsEvent($this->input->reveal(), $this->output->reveal());

        $this->assertSame($version, $event->version());

        return [
            'event' => $event,
            'version' => $version,
        ];
    }

    /**
     * @depends testConstructorSetsVersionFromInputArgument
     */
    public function testConstructorSetsTagNameBasedOnVersionIfNoTagNameOptionPresent(array $dependencies)
    {
        $event = $dependencies['event'];
        $version = $dependencies['version'];
        $this->assertSame($version, $event->tagName());
    }

    public function testConstructorSetsTagNameFromInputOptionWhenPresent()
    {
        $version = '1.2.3';
        $tagName = 'v1.2.3';
        $this->input->getArgument('version')->willReturn($version);
        $this->input->getOption('tagname')->willReturn($tagName);

        $event = new ValidateRequirementsEvent($this->input->reveal(), $this->output->reveal());

        $this->assertSame($version, $event->version());
        $this->assertSame($tagName, $event->tagName());
    }

    public function testPropagationIsNotStoppedByDefault()
    {
        $event = $this->createEvent();

        $this->assertFalse($event->isPropagationStopped());
    }

    public function testRequirementsAreNotMetByDefault()
    {
        $event = $this->createEvent();

        $this->assertFalse($event->requirementsMet());
    }

    public function testConfigIsEmptyByDefault()
    {
        $event = $this->createEvent();

        $this->assertNull($event->config());
    }

    public function testCouldNotFindTagDoesNotMeetRequirementsStopsPropagationAndSendsOutput()
    {
        $event = $this->createEvent();

        $event->couldNotFindTag();

        $this->assertFalse($event->requirementsMet());
        $this->assertTrue($event->isPropagationStopped());
        $this->output
            ->writeln(Argument::containingString('No tag matching'))
            ->shouldHaveBeenCalled();
    }

    public function testSettingConfigWithoutTokenDoesNotMeetRequirementsOrStopPropagation()
    {
        $event = $this->createEvent();
        $config = $this->prophesize(Config::class);
        $config->token()->willReturn('');

        $event->setConfig($config->reveal());

        $this->assertSame($config->reveal(), $event->config());
        $this->assertFalse($event->requirementsMet());
        $this->assertFalse($event->isPropagationStopped());
    }

    public function configFilePaths() : iterable
    {
        $globalPath = '/home/user';
        $localPath = '/home/user/repo';

        yield 'global' => [true, $globalPath, $localPath, '/home/user/.keep-a-changelog/config.ini'];
        yield 'local' => [false, $globalPath, $localPath, '/home/user/repo/.keep-a-changelog.ini'];
    }

    /**
     * @dataProvider configFilePaths
     */
    public function testTokenNotFoundDoesNotMeetRequirementsStopsPropagationAndSendsOutput(
        bool $globalOption,
        string $globalPath,
        string $localPath,
        string $expected
    ) {
        $this->input->getOption('global')->willReturn($globalOption);
        $event = $this->createEvent();
        $event->globalPath = $globalPath;
        $event->localPath = $localPath;

        $event->tokenNotFound();

        $this->assertFalse($event->requirementsMet());
        $this->assertTrue($event->isPropagationStopped());
        $this->output
            ->writeln(Argument::containingString($expected))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('--token option'))
            ->shouldHaveBeenCalled();
    }

    public function testRequirementsMetWhenTagExistsConfigFoundAndTokenPresentInConfig()
    {
        $config = $this->prophesize(Config::class);
        $config->token()->willReturn('some-token');
        $event = $this->createEvent();

        $event->setConfig($config->reveal());

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->requirementsMet());
        $this->assertSame($config->reveal(), $event->config());
    }
}
