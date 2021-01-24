# keep-a-changelog

[![Build Status](https://secure.travis-ci.org/phly/keep-a-changelog.svg?branch=master)](https://secure.travis-ci.org/phly/keep-a-changelog)
[![Coverage Status](https://coveralls.io/repos/github/phly/keep-a-changelog/badge.svg?branch=master)](https://coveralls.io/github/phly/keep-a-changelog?branch=master)

This project provides tooling support for working with [Keep A
Changelog](https://keepachangelog.com).

## Installation

### Local install via Composer

Run the following to install this library:

```bash
$ composer require phly/keep-a-changelog
```

### Global install via Composer

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

You can add the following line to your shell profile to auto-add it to your PATH:

```bash
export PATH=$(composer global config bin-dir --absolute):$PATH
```

Once setup this way, you can call `keep-a-changelog` instead of
`./vendor/bin/keep-a-changelog`.

### PHAR file

Since version 2.3.0, we have offered standalone PHAR binaries. These are
available under the downloads section for each release. To retrieve the latest,
you can always use the following url:

- https://github.com/phly/keep-a-changelog/releases/latest/download/keep-a-changelog.phar

Make the file executable (e.g., `chmod 755 keep-a-changelog.phar`), place it in
your `$PATH`, and, optionally, remove the `.phar` suffix

At the time of writing, the PHAR distribution is not yet capable of
self-updating or checking for updates.

## Usage

Invocation will be via one of the following:

- If you have installed via Composer within your project: `./vendor/bin/keep-a-changelog`
- If you have installed globally via Composer, and have added the Composer
  script path to  your `$PATH`: `keep-a-changelog`.
- If you have downloaded the PHAR file and put it in your `$PATH`: either
  `keep-a-changelog.phar` or, if you removed the `.phar` file extension,
  `keep-a-changelog`.

From here forward, we will use `keep-a-changelog` to invoke the command;
substitute the appropriate command invocation based on your install.

You may get a list of commands by running:

```bash
$ keep-a-changelog
```

From there, you can get help for individual commands using:

```bash
$ keep-a-changelog help <command>
```

For more information, [please visit the documentation](https://phly.github.io/keep-a-changelog/#available-commands).
