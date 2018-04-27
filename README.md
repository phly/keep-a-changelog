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

All options allow specifying the option `--file` (or its alias `-f`) to indicate
an alternate changelog file to create or manipulate; if not present,
`CHANGELOG.md` in the current directory is assumed.

You can specify an alternative provider besides GitHub passing the --provider argument
to commands. Currently only `tag`, `release` and `entry` commands need this option.

The available providers are:
- GitHub (default)
- GitLab

Currently supported commands include:

- `ready` will set the planned release date for the most recent changelog entry.

- `tag` allows tagging a release based on the latest version discovered in the
  `CHANGELOG.md` file. The tag will contain the changelog entry for that version
  within the commit message.

- `release` will push a tag to a provider (GitHub or GitLab), and then create a
  release for it, using the changelog entry for the release.

- `bump` and `bump:bugfix` will prepend a new changelog entry for a new bugfix
  release, based on the latest release found in the `CHANGELOG.md` file.

- `bump:minor` will prepend a new changelog entry for a new minor
  release, based on the latest release found in the `CHANGELOG.md` file.

- `bump:major` will prepend a new changelog entry for a new major
  release, based on the latest release found in the `CHANGELOG.md` file.

- `bump:to-version` will prepend a new changelog entry, using the version
  specified on the command line.

- `entry:added` will add a new changelog entry to the Added section of the
  current changelog within the `CHANGELOG.md` file.

- `entry:changed` will add a new changelog entry to the Changed section of the
  current changelog within the `CHANGELOG.md` file.

- `entry:deprecated` will add a new changelog entry to the Deprecated section of
  the current changelog within the `CHANGELOG.md` file.

- `entry:removed` will add a new changelog entry to the Removed section of the
  current changelog within the `CHANGELOG.md` file.

- `entry:fixed` will add a new changelog entry to the Fixed section of the
  current changelog within the `CHANGELOG.md` file.

- `edit` will open the most recent changelog section in the system editor
  to allow editing the full entry at once.

For a list of required parameters and all options for a command, run:

```bash
$ ./vendor/bin/keep-a-changelog help <command>
```
