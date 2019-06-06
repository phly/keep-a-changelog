# keep-a-changelog

[![Build Status](https://secure.travis-ci.org/phly/keep-a-changelog.svg?branch=master)](https://secure.travis-ci.org/phly/keep-a-changelog)
[![Coverage Status](https://coveralls.io/repos/github/phly/keep-a-changelog/badge.svg?branch=master)](https://coveralls.io/github/phly/keep-a-changelog?branch=master)

This project provides tooling support for working with [Keep A
Changelog](https://keepachangelog.com).

## Installation

Run the following to install this library:

```bash
$ composer require phly/keep-a-changelog
```

Alternately, install globally, for use with any repository:

```php
$ composer global require phly/keep-a-changelog
```

If you install globally, ensure you add global composer vendor binaries
directory to your `$PATH` environment variable. You can get its location with
following command:

```bash
$ composer global config bin-dir --absolute
```

Once setup this way, you can call `keep-a-changelog` instead of
`./vendor/bin/keep-a-changelog`.

## Usage

You may get a list of commands by running:

```bash
$ ./vendor/bin/keep-a-changelog
```

From there, you can get help for individual commands using:

```bash
$ ./vendor/bin/keep-a-changelog help <command>
```

## Configuration Files

The tool can use a combination of globally available (to the user) configuration
files, local to the project configuration files, or input options.

User configuration files are kept in `$XDG_CONFIG_HOME/keep-a-changelog.ini`,
which is generally `$HOME/.config/keep-a-changelog.ini`.

Local project configuration files are kept in `./.keep-a-changelog.ini`,
relative to where you run the command.

These files can be created using the following:

```bash
$ keep-a-changelog config:create [--global|-g] [--local|-l]
```

If both flags are provided, both files will be created.

Configuration files are in [INI file format](https://en.wikipedia.org/wiki/INI_file),
and support the following sections and keys:

```dosini
[defaults]

; Global config: Default changelog file to use.
; Local config: Specific changelog file to use for this project.
changelog_file = CHANGELOG.md

; Global config: Default provider to use.
; Local config: Specific provider to use for this project.
provider = github

; Global config: default Git remote to push tags to.
; Local config: specificc Git remote to push tags to for this project.
remote = origin

[providers]
github[class] = Phly\KeepAChangelog\Provider\GitHub
github[token] =
gitlab[class] = Phly\KeepAChangelog\Provider\GitLab
gitlab[token] =
```

Providers are covered in a [later section](#providers).

Local configuration files support additional keys in the `[defaults`] section:

```dosini
; local .keep-a-changelog.ini file
[defaults]

; Specify the package name (used for naming and pushing releases, and generating
; issue and patch links:
package = package/name
```

Local configuration **MUST NOT** contain provider tokens. Any tokens discovered
in a local configuration file _will be ignored_.

> Local configuration files are meant to be checked in to a project, and are
> thus generally insecure for the purpose of storing API tokens. User
> configuration is generally restricted to reading by the user only, making it a
> more sound location to store this sensitive information.

### Package name configuration

As noted above, you can specify a package name in your local configuration file.

Alternately, if none is discovered there and the `--package` option is not
provided to a command that requires it, the tooling will look for a package name
in one of the following:

- `composer.json` (`name` key)
- `package.json` (`name` key)

### Remote name configuration

As noted above, you can specify the name of a git remote to push tags to in
either your global or your local configuration file.

Alternately, if neither file defines the value, and the `--remote` option is not
provided to a command that requires it, the tooling will look for the remote
name by trying to match against remotes listed in your `.git/config`, based on
the selected provider and package name.

## Providers

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
tool about it. This is done in your configuration files.

Provider configuration is done in the `[providers]` section of your
configuration file, and each has the following structure:

```dosini
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

## Supported commands

- `bump`, `bump:bugfix`, and `bump:patch`: Create a new changelog entry for the
  next bugfix release.
- `bump:minor`: Create a new changelog entry for the next minor release.
- `bump:major`: Create a new changelog entry for the next major release.
- `bump:to-version`: Create a new changelog entry for a user-specified version.
- `changelog:edit-links` (since 2.1.0): Edit reference links from the end of the
  changelog file.
- `changelog:new`: Create a new changelog file.
- `config:create`: Create one or more configuration files.
- `config:edit`: Edit a configuration file.
- `config:remove`: Remove a configuration file.
- `config:show`: Show one or more configuration files.
- `entry:added`: Create a changelog entry in the "Added" section.
- `entry:changed`: Create a changelog entry in the "Changed" section.
- `entry:deprecated`: Create a changelog entry in the "Deprecated" section.
- `entry:removed`: Create a changelog entry in the "Removed" section.
- `entry:fixed`: Create a changelog entry in the "Fixed" section.
- `version:edit`: Edit a full changelog version and its entries.
- `version:list`: List all changelog versions currently in the file.
- `version:ready`: Mark a changelog version ready for release by setting its date.
- `version:release`: create a new release on your given provider, using the
  relevant changelog entry. This command also pushes the related tag.
- `version:remove`: Remove the given changelog version and its entries.
- `version:show`: Show the given changelog version and its entries.
- `version:tag`: Create a new tag, using the relevant changelog entry. Creates
  signed tags.

For a list of required parameters and all options for a command, run:

```bash
$ keep-a-changelog help <command>
```
