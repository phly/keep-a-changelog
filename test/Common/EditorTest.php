<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\Editor;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class EditorTest extends TestCase
{
    public function setUp()
    {
        $this->output = $this->prophesize(OutputInterface::class);
    }

    public function testSpawnsEditorForGivenFilename()
    {
        $editor = new Editor();
        $editor->procOpen = function (string $command, array $streams, array &$pipes) {
            TestCase::assertSame("vim 'CHANGELOG.md'", $command);
            TestCase::assertSame([STDIN, STDOUT, STDERR], $streams);
            TestCase::assertSame([], $pipes);
            return 'CHANGELOG.md';
        };
        $editor->procClose = function ($process) : int {
            TestCase::assertSame('CHANGELOG.md', $process);
            return 0;
        };

        $this->output->writeln(Argument::containingString('Executing'))->shouldBeCalled();

        $this->assertSame(0, $editor->spawnEditor(
            $this->output->reveal(),
            'vim',
            'CHANGELOG.md'
        ));
    }
}
