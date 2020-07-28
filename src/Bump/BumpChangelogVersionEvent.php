<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class BumpChangelogVersionEvent extends AbstractEvent
{
    public const UNRELEASED = 'Unreleased';

    /**
     * ChangelogBump method to use when bumping version.
     *
     * @var null|string
     */
    private $bumpMethod;

    /**
     * Version to bump to; null implies latest.
     *
     * @var null|string
     */
    private $version;

    /**
     * One or the other of $bumpMethod or $version MUST be set, but
     * not both.
     *
     * @throws Exception\InvalidChangelogBumpCriteriaException
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        ?string $bumpMethod = null,
        ?string $version = null
    ) {
        if (
            (! $bumpMethod && ! $version)
            || ($bumpMethod && $version)
        ) {
            throw Exception\InvalidChangelogBumpCriteriaException::forCriteria($bumpMethod, $version);
        }

        if (! $version && $bumpMethod === self::UNRELEASED) {
            $version    = self::UNRELEASED;
            $bumpMethod = null;
        }

        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
        $this->bumpMethod = $bumpMethod;
        $this->version    = $version;
    }

    public function isPropagationStopped(): bool
    {
        return $this->failed;
    }

    public function bumpMethod(): ?string
    {
        return $this->bumpMethod;
    }

    public function version(): ?string
    {
        return $this->version;
    }

    public function bumpedChangelog(string $version): void
    {
        $this->version = $version;
        $this->output()->writeln(sprintf(
            '<info>Bumped changelog version to %s</info>',
            $version
        ));
    }
}
