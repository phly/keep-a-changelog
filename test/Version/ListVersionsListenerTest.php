<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ListVersionsEvent;
use Phly\KeepAChangelog\Version\ListVersionsListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class ListVersionsListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testEmitsAllVersionsFoundWithCorrespondingDates()
    {
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);

        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn(__DIR__ . '/../_files/CHANGELOG.md');

        $event = $this->prophesize(ListVersionsEvent::class);
        $event->output()->will([$output, 'reveal']);
        $event->config()->will([$config, 'reveal']);

        $listener = new ListVersionsListener();

        $this->assertNull($listener($event->reveal()));
        $output
            ->writeln(Argument::containingString('Found the following versions'))
            ->will(function () use ($output) {
                $expected = [
                    '2.0.0' => 'TBD',
                    '1.1.0' => '2018-03-23',
                    '0.1.0' => '2018-03-23',
                ];
                foreach ($expected as $version => $date) {
                    $output
                        ->writeln(Argument::that(function ($message) use ($version, $date) {
                            TestCase::assertMatchesRegularExpression(
                                sprintf('/%s\s+\(release date: %s\)/', $version, $date),
                                $message
                            );
                            return $message;
                        }))
                        ->shouldHaveBeenCalled();
                }
            })
            ->shouldHaveBeenCalled();
    }
}
