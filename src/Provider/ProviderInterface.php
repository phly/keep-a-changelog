<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

interface ProviderInterface
{
    /**
     * Consumers can use this to test if the provider has everything it needs
     * to create a new release, thus avoiding exceptions.
     */
    public function canCreateRelease(): bool;

    /**
     * Consumers can use this to test if the provider has everything it needs
     * to generate a patch link, thus avoiding exceptions.
     */
    public function canGenerateLinks(): bool;

    /**
     * @return null|string URL to the created release; null indicates failure
     *     generally.
     * @throws Exception\MissingPackageNameException
     * @throws Exception\MissingTokenException
     */
    public function createRelease(
        string $releaseName,
        string $tagName,
        string $changelog
    ): ?string;

    /**
     * This method should generate the full markdown link to an issue.
     *
     * As an example of something it could generate:
     *
     * <code>
     * [#17](https://github.com/not-an-org/not-a-repo/issues/17)
     * </code>
     *
     * @throws Exception\MissingPackageNameException
     */
    public function generateIssueLink(int $issueIdentifier): string;

    /**
     * This method should generate the full markdown link to a patch.
     *
     * As an example of something it could generate:
     *
     * <code>
     * [#17](https://github.com/not-an-org/not-a-repo/pull/17)
     * </code>
     *
     * @throws Exception\MissingPackageNameException
     */
    public function generatePatchLink(int $patchIdentifier): string;

    /**
     * Set the package name to use in links and when creating the release name.
     */
    public function setPackageName(string $package): void;

    /**
     * Set the authentication token to use for API calls to the provider.
     */
    public function setToken(string $token): void;

    /**
     * Set the base URL to use for API calls to the provider.
     *
     * Generally, this should only be the scheme + authority.
     */
    public function setUrl(string $url): void;
}
