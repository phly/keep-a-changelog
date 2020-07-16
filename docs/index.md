---
title: Home
permalink: /index.html
layout: default
nav_order: 1
---

# phly/keep-a-changelog

> Manage your [Keep-A-Changelog](https://keepachangelog.com)-formatted
> `CHANGELOG.md` via CLI tooling!

Changelog files grow constantly, and the amount of markup in them can cause
editors and mergetools to balk at times. Additionally, 99% of the time as a
maintainer, all you really want to do is:

- Add an entry to the relevant portion of the most recent version in the
  changelog.
- View or edit a changelog version.
- Extract a changelog version for use in a tag or release.

This tool provides those features and more, allowing you to quickly and easily
handle changelogs for your project.

## Requirements

This tool is written in [PHP](http://php.net), and requires version 7.2 or later.

## Installation

Installation is via [Composer](https://getcomposer.org), and it may be installed
globally or within a project.

### Per-Project Installation

Choose this option if you want to ensure all maintainers of your project use the
tooling to manage the changelog.

```bash
$ composer require --dev phly/keep-a-changelog
```

Invocation will then be via `./vendor/bin/keep-a-changelog`; when you see
`keep-a-changelog` in examples in this documentation, this is what is meant.

To update, run:

```bash
$ composer update phly/keep-a-changelog
```

or, to ensure you get at least a specific version:

```bash
# Require at least version 2.1.0:
$ composer require "phly/keep-a-changelog:^2.1.0"
```

### Global Installation

When installing globally, you have two options: using `composer global`, or
using a local checkout.

#### Composer Global

To use composer:

```bash
$ composer global require phly/keep-a-changelog
```

If you install globally, ensure you add global composer vendor binaries
directory to your `$PATH` environment variable. You can get its location with
following command:

```bash
$ composer global config bin-dir --absolute
```

#### Local Checkout

Alternately, you can check it out locally, and add its binary to your path. I
recommend:

- Checking it out to an `$XDG_DATA_DIRS` location, such as in the `$HOME/.local` tree.
- Creating a script on your `$PATH` (e.g. `$HOME/.local/bin`) that invokes the
  package binary using a specific PHP version. I recommend this in the
  off-chance that you use multiple PHP versions, as it guarantees that the PHP
  version used will work with it.

```bash
# Clone the repo:
$ mkdir -p $HOME/.local/src/github.com/phly
$ cd $HOME/.local/src/github.com/phly
$ git clone https://github.com/phly/keep-a-changelog.git
$ cd keep-a-changelog
# Checkout the latest tagged version; e.g. 2.1.0:
$ git checkout 2.1.0
# Install dependencies:
$ composer install
# Create the script:
$ echo "#!/usr/bin/bash\n/usr/bin/php7.2 \$HOME/.local/src/github.com/phly/keep-a-changelog/bin/keep-a-changelog \$@" > $HOME/.local/bin/keep-a-changelog
$ chmod 755 $HOME/.local/bin/keep-a-changelog
```

To update:

```bash
$ cd $HOME/.local/src/github.com/phly/keep-a-changelog
$ git fetch origin
$ git checkout <new version>
$ composer install
```

## Usage

To use the tooling:

```bash
$ keep-a-changelog
```

This will provide you with a list of commands. You can get detailed help for
each command using:

```bash
$ keep-a-changelog help <command>
```

### Available Commands:

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
- `unreleased:create`: Create a new "Unreleased" entry at the top of thee
  changelog file.
- `unreleased:promote`: Rename the "Unreleased" entry with the provided version,
  setting the release date.
- `version:edit`: Edit a full changelog version and its entries.
- `version:list`: List all changelog versions currently in the file.
- `version:ready`: Mark a changelog version ready for release by setting its date.
- `version:release`: create a new release on your given provider, using the
  relevant changelog entry. This command also pushes the related tag.
- `version:remove`: Remove the given changelog version and its entries.
- `version:show`: Show the given changelog version and its entries.
- `version:tag`: Create a new tag, using the relevant changelog entry. Creates
  signed tags.

## But wait, there's more!

You can configure the changelog file to use, the package name to use when
creating releases or generating links for changelog entries, the git remote to
push tags to, and where to create releases.

> - Interested? [Read more about configuration.](config.md)

When generating issue and patch links to use with your changelog entries, and
when creating releases, the tooling works with GitHub and GitLab out-of-the-box,
but you can also extend it! 

> - Interested? [Read more about providers.](providers.md)

