<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\Editor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

use const STDERR;
use const STDIN;
use const STDOUT;

class EditorTest extends TestCase
{
    public function testSpawnsEditorForGivenFilename()
    {
        $editor            = new Editor();
        $editor->procOpen  = function (string $command, array $streams, array &$pipes) {
            TestCase::assertSame("vim 'CHANGELOG.md'", $command);
            TestCase::assertSame([STDIN, STDOUT, STDERR], $streams);
            TestCase::assertSame([], $pipes);
            return 'CHANGELOG.md';
        };
        $editor->procClose = function ($process): int {
            TestCase::assertSame('CHANGELOG.md', $process);
            return 0;
        };

        /** @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with($this->stringContains('Executing'));

        $this->assertSame(0, $editor->spawnEditor($output, 'vim', 'CHANGELOG.md'));
    }
}
