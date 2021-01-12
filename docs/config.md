---
title: Configuration
permalink: /config.html
layout: default
nav_order: 2
---

# Configuration

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

```ini
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

Providers are covered in [another chapter](providers.md).

Local configuration files support additional keys in the `[defaults]` section:

```ini
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
provided for a command that requires it, the tooling will look for a package name
in one of the following:

- `composer.json` (`name` key)
- `package.json` (`name` key)

### Remote name configuration

As noted above, you can specify the name of a git remote to push tags to in
either your global or your local configuration file.

Alternately, if neither file defines the value, and the `--remote` option is not
provided for a command that requires it, the tooling will look for the remote
name by trying to match against remotes listed in your `.git/config`, based on
the selected provider and package name.
