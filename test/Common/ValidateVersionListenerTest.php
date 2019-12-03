<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use PHPUnit\Framework\TestCase;

class ValidateVersionListenerTest extends TestCase
{
    protected function setUp() : void
    {
        $this->event = $this->prophesize(VersionAwareEventInterface::class);
    }

    public function testEmptyVersionIsInvalid()
    {
        $this->event->version()->willReturn(null);
        $this->event->versionIsInvalid('')->shouldBeCalled();

        $listener = new ValidateVersionListener();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function versions() : iterable
    {
        yield 'major-only'     => ['1', $isFailure = true];
        yield 'minor-only'     => ['1.1', $isFailure = true];
        yield 'malformed'      => ['1.1.1abcde', $isFailure = true];

        yield 'bugfix'         => ['1.1.1', $isFailure = false];
        yield 'minor'          => ['1.2.0', $isFailure = false];
        yield 'major'          => ['2.0.0', $isFailure = false];

        yield 'alpha-wrong'    => ['2.0.0alphaBeta', $isFailure = true];
        yield 'alpha-long'     => ['2.0.0alpha1', $isFailure = false];
        yield 'alpha-short'    => ['2.0.0a1', $isFailure = false];
        yield 'alpha-long-uc'  => ['2.0.0ALPHA1', $isFailure = false];
        yield 'alpha-short-uc' => ['2.0.0A1', $isFailure = false];

        yield 'beta-wrong'     => ['2.0.0betaRC', $isFailure = true];
        yield 'beta-long'      => ['2.0.0beta2', $isFailure = false];
        yield 'beta-short'     => ['2.0.0b2', $isFailure = false];
        yield 'beta-long-uc'   => ['2.0.0BETA2', $isFailure = false];
        yield 'beta-short-uc'  => ['2.0.0B2', $isFailure = false];

        yield 'rc-wrong'       => ['2.0.0rcDEV', $isFailure = true];
        yield 'rc-long'        => ['2.0.0rc3', $isFailure = false];
        yield 'rc-long-uc'     => ['2.0.0RC3', $isFailure = false];

        yield 'dev-wrong'      => ['2.0.0devPATCH', $isFailure = true];
        yield 'dev-long'       => ['2.0.0dev4', $isFailure = false];
        yield 'dev-long-uc'    => ['2.0.0DEV4', $isFailure = false];

        yield 'patch-wrong'    => ['2.0.0patchFOO', $isFailure = true];
        yield 'patch-long'     => ['2.0.0patch5', $isFailure = false];
        yield 'patch-short'    => ['2.0.0pl5', $isFailure = false];
        yield 'patch-shorter'  => ['2.0.0p5', $isFailure = false];
        yield 'patch-long-uc'  => ['2.0.0PATCH5', $isFailure = false];
        yield 'patch-short-uc' => ['2.0.0PL5', $isFailure = false];
        yield 'patch-shorter-uc' => ['2.0.0P5', $isFailure = false];
    }

    /**
     * @dataProvider versions
     */
    public function testIdentifiesVersionsCorrectly(string $version, bool $isFailure)
    {
        $this->event->version()->willReturn($version);
        $isFailure
            ? $this->event->versionIsInvalid($version)->shouldBeCalled()
            : $this->event->versionIsInvalid($version)->shouldNotBeCalled();

        $listener = new ValidateVersionListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
