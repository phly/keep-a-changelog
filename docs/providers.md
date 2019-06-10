---
permalink: /providers.html
title: Providers
layout: default
nav_order: 3
---

# Providers

Providers refer to the location where you push releases and create issues and
patches; this is generally speaking the _shared repository_ for your project.

For the purpose of this tooling, providers need to be able to generate both
issue and patch links, and create releases. All providers thus implement the
following interface:

```php
namespace Phly\KeepAChangelog\Provider;

interface ProviderInterface
{
    /**
     * Consumers can use this to test if the provider has everything it needs
     * to create a new release, thus avoiding exceptions.
     */
    public function canCreateRelease() : bool;

    /**
     * Consumers can use this to test if the provider has everything it needs
     * to generate a patch link, thus avoiding exceptions.
     */
    public function canGenerateLinks() : bool;

    /**
     * @return string URL to the created release.
     * @throws Exception\MissingPackageNameException
     * @throws Exception\MissingTokenException
     */
    public function createRelease(
        string $releaseName,
        string $tagName,
        string $changelog
    ) : ?string;

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
    public function generateIssueLink(int $issueIdentifier) : string;

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
    public function generatePatchLink(int $patchIdentifier) : string;

    /**
     * Set the package name to use in links and when creating the release name.
     */
    public function setPackageName(string $package) : void;

    /**
     * Set the authentication token to use for API calls to the provider.
     */
    public function setToken(string $token) : void;

    /**
     * Set the base URL to use for API calls to the provider.
     *
     * Generally, this should only be the scheme + authority.
     */
    public function setUrl(string $url) : void;
}
```

### Preparing your provider for use

In order to use a custom provider, you will need to ensure it is autoloadable.
That can be accomplished in one of two ways.

If you are installing `phly/keep-a-changelog` directly within your project, the
provider only needs to be autoloadable within your project. That can be
accomplished by ensuring the class is developed as an autoloadable class
directly in the project, or by installing it via a Composer package as a sibling
to the keep-a-changelog dependency.

If you are installing 'phly/keep-a-changelog' globally, you will need to install
a package with your custom provider globally as well.

### Configuring your provider

Once you have ensured your provider is autoloadable, you will need to tell the
tool about it. This is done in your [configuration files](config.md).

Provider configuration is done in the `[providers]` section of your
configuration file, and each has the following structure:

```ini
[providers]
; Required:
<name>[class] = Fully\Qualified\Provider\ClassName

; Optional; only if your provider supports alternate URL endpoints
; for creating releases:
<name>[url] = base-url-for-api-calls

; Optional, and only in global configuration; authorization token to
; use when making API calls.
<name>[token] = api-authorization-token
```

Where `<name>` is a unique shorthand name for the provider. This `<name>` can
then be used later:

- To specify the default provider to use, or the provider specific to your
  project.
- When specifying a `--provider` input option while invoking a command.

### Input options

For commands that require a provider, the following input options are exposed:

- `--provider`, to specify a provider short name to use for the current
  invocation.
- `--provider-class`, to specify a specific `ProviderInterface` implementation
  to use for the current invocation.
- `--provider-url`, to specify a custom base API URL to use for the current
  invocation.
- `--provider-token`, to specify an authorization token to use for the current
  invocation.

### Default providers

The default providers available are:

- github (default)
- gitlab
