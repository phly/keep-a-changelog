<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Phly\KeepAChangelog\Config;
use Psr\EventDispatcher\EventDispatcherInterface;

use function sprintf;

abstract class AbstractEvent implements EventInterface
{
    use IOTrait;

    /** @var null|Config */
    protected $config;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * Whether or not the event failed.
     *
     * @var bool
     */
    protected $failed = false;

    public function failed(): bool
    {
        return $this->failed;
    }

    public function changelogFileIsUnreadable(string $changelogFile): void
    {
        $this->failed = true;
        $this->output()->writeln(sprintf(
            '<error>Changelog file "%s" is unreadable.</error>',
            $changelogFile
        ));
    }

    public function configurationIncomplete(): void
    {
        $this->failed = true;
    }

    public function missingConfiguration(): bool
    {
        return null === $this->config;
    }

    /**
     * Update the event with the discovered configuration instance.
     */
    public function discoveredConfiguration(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * Return the configuration instance, if available.
     */
    public function config(): ?Config
    {
        return $this->config;
    }

    /**
     * Configurable events should be passed the event dispatcher, so that the
     * configuration listener can dispatch its internal events in order to
     * aggregate configuration.
     */
    public function dispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}
