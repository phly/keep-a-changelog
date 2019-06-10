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

For more information, [please visit the documentation](https://phly.github.io/keep-a-changelog/).
