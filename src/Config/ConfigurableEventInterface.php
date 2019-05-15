<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\IOInterface;
use Psr\EventDispatcher\StoppableEventInterface;

interface ConfigurableEventInterface extends
    IOInterface,
    StoppableEventInterface
{
    /**
     * Notify the event that one or more required configuration
     * items/input options were missing.
     *
     * This method should cause propagation to stop.
     */
    public function configurationIncomplete() : void;

    /**
     * Report whether the event is missing configuration or not.
     */
    public function missingConfiguration() : bool;

    public function discoveredConfiguration(Config $config) : void;

    public function config() : ?Config;
}
