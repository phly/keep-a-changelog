<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

interface ProviderNameProviderInterface
{
    /**
     * Return the name of the provider (e.g. github, gitlab)
     */
    public function getName() : string;

    /**
     * Return the domain name of the provider (e.g., github.com)
     */
    public function getDomainName() : string;

    /**
     * Create a new instance with the given domain name.
     */
    public function withDomainName(string $domain) : self;
}
