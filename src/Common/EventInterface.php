<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Phly\KeepAChangelog\Config;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

interface EventInterface extends
    IOInterface,
    StoppableEventInterface
{
    /**
     * Indicate whether the event failed.
     *
     * Generally speaking, this should return true if propagation has been stopped.
     */
    public function failed(): bool;

    /**
     * Notify the event that the changelog file is unreadable.
     *
     * This method should cause propagation to stop.
     */
    public function changelogFileIsUnreadable(string $changelogFile): void;

    /**
     * Notify the event that one or more required configuration
     * items/input options were missing.
     *
     * This method should cause propagation to stop.
     */
    public function configurationIncomplete(): void;

    /**
     * Report whether the event is missing configuration or not.
     */
    public function missingConfiguration(): bool;

    /**
     * Update the event with the discovered configuration instance.
     */
    public function discoveredConfiguration(Config $config): void;

    /**
     * Return the configuration instance, if available.
     */
    public function config(): ?Config;

    /**
     * Configurable events should be passed the event dispatcher, so that the
     * configuration listener can dispatch its internal events in order to
     * aggregate configuration.
     */
    public function dispatcher(): EventDispatcherInterface;
}
