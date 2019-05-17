<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\CreateReleaseEvent;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateReleaseEventTest extends TestCase
{
    public function setUp()
    {
        $this->input    = $this->prophesize(InputInterface::class);
        $this->output   = $this->prophesize(OutputInterface::class);
        $this->provider = $this->prophesize(ProviderInterface::class);
    }

    public function createEvent(
        string $package = 'some/package',
        string $version = '1.2.3',
        string $changelog = 'this is the changelog'
    ) : CreateReleaseEvent {
        return new CreateReleaseEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->provider->reveal(),
            $version,
            $changelog,
            $package
        );
    }

    public function testEventIsStoppable()
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
    }

    public function testPropagationIsNotStoppedByDefault()
    {
        $event = $this->createEvent();
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testReleaseIsNotCreatedByDefault()
    {
        $event = $this->createEvent();
        $this->assertFalse($event->wasCreated());
    }

    public function testChangelogIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame('this is the changelog', $event->changelog());
    }

    public function testPackageIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame('some/package', $event->package());
    }

    public function testProviderIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame($this->provider->reveal(), $event->provider());
    }

    public function testReleaseIsNotSetByDefault()
    {
        $event = $this->createEvent();
        $this->assertNull($event->release());
    }

    public function testVersionIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame('1.2.3', $event->version());
    }

    public function testReleaseNameIsNotSetByDefault()
    {
        $event = $this->createEvent();
        $this->assertNull($event->releaseName());
    }

    public function testCanSetReleaseName()
    {
        $event = $this->createEvent();
        $event->setReleaseName('Name of the release');
        $this->assertSame('Name of the release', $event->releaseName());
    }

    public function testMarkingReleaseCreatedStopsPropagation()
    {
        $event = $this->createEvent();
        $event->releaseCreated('url-to-release');

        $this->assertSame('url-to-release', $event->release());
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->wasCreated());
    }

    public function testMarkingReleaseCreationErrorStopsPropagationWithoutRelease()
    {
        $error = new RuntimeException('error-creating-release', 400);
        $event = $this->createEvent();
        $this->output
            ->writeln(Argument::containingString('Error creating release!'))
            ->shouldBeCalled();
        $this->output
            ->writeln(Argument::containingString('error was caught'))
            ->shouldBeCalled();
        $this->output
            ->writeln(Argument::containingString($error->getMessage()))
            ->shouldBeCalled();

        $this->assertNull($event->errorCreatingRelease($error));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->wasCreated());
    }

    public function testMarkingUnexpectedProviderStopsPropagationWithoutRelease()
    {
        $event    = $this->createEvent();
        $expected = gettype($this->provider->reveal());

        $this->output
            ->writeln(Argument::containingString('Error creating release!'))
            ->shouldBeCalled();
        $this->output
            ->writeln(Argument::containingString(sprintf(
                'Provider of type "%s"',
                $expected
            )))
            ->shouldBeCalled();

        $this->assertNull($event->unexpectedProviderResult());

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->wasCreated());
    }
}
