<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use Phly\KeepAChangelog\ConfigCommand\ShowMergedConfigListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use function parse_ini_string;

class ShowMergedConfigListenerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $voidReturn = function () {
        };

        $this->event = $this->prophesize(ShowConfigEvent::class);
        $this->event->configIsNotReadable(Argument::any(), 'global')->will($voidReturn);
        $this->event->configIsNotReadable(Argument::any(), 'local')->will($voidReturn);
        $this->event->displayMergedConfig(Argument::any())->will($voidReturn);
    }

    public function testListenerReturnsEarlyIfMergedConfigIsNotRequested()
    {
        $this->event->showMerged()->willReturn(false);

        $listener = new ShowMergedConfigListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->configIsNotReadable(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
        $this->event->displayMergedConfig(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerReturnsEarlyIfGlobalConfigIsNotReadable()
    {
        $this->event->showMerged()->willReturn(true);

        $listener             = new ShowMergedConfigListener();
        $listener->configRoot = __DIR__;

        $this->assertNull($listener($this->event->reveal()));

        $this->event->configIsNotReadable(Argument::any(), 'global')->shouldHaveBeenCalled();
        $this->event->configIsNotReadable(Argument::any(), 'local')->shouldNotHaveBeenCalled();
        $this->event->displayMergedConfig(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerReturnsEarlyIfLocalConfigIsNotReadable()
    {
        $this->event->showMerged()->willReturn(true);

        $listener                  = new ShowMergedConfigListener();
        $listener->configRoot      = __DIR__ . '/../_files/config';
        $listener->localConfigRoot = __DIR__;

        $this->assertNull($listener($this->event->reveal()));

        $this->event->configIsNotReadable(Argument::any(), 'global')->shouldNotHaveBeenCalled();
        $this->event->configIsNotReadable(Argument::any(), 'local')->shouldHaveBeenCalled();
        $this->event->displayMergedConfig(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventToDisplayMergedConfig()
    {
        $this->event->showMerged()->willReturn(true);

        $listener                  = new ShowMergedConfigListener();
        $listener->configRoot      = __DIR__ . '/../_files/config';
        $listener->localConfigRoot = __DIR__ . '/../_files/config/local';

        $this->assertNull($listener($this->event->reveal()));

        $this->event->configIsNotReadable(Argument::any(), 'global')->shouldNotHaveBeenCalled();
        $this->event->configIsNotReadable(Argument::any(), 'local')->shouldNotHaveBeenCalled();
        $this->event
            ->displayMergedConfig(Argument::that(function ($configString) {
                $config = parse_ini_string($configString, true);
                TestCase::assertSame('CHANGELOG.txt', $config['defaults']['changelog_file']);
                TestCase::assertSame('github', $config['defaults']['provider']);
                TestCase::assertSame('origin', $config['defaults']['remote']);
                TestCase::assertSame('https://github.mwop.net', $config['providers']['github']['url']);
                return $configString;
            }))
            ->shouldHaveBeenCalled();
    }
}
