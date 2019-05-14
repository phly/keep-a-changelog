# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.6.1 - 2019-05-14

### Added

- Nothing.

### Changed

- [#48](https://github.com/phly/keep-a-changelog/pull/48) adds the string `: ` to then end of all question prompts to make them more readable.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#52](https://github.com/phly/keep-a-changelog/pull/52) removes the `Throwable $e` argument to `MissingTagException::forUnverifiedTagOnGithub()`
  as no `Throwable` was available in the context in which it was called.

- [#47](https://github.com/phly/keep-a-changelog/pull/47) fixes a bug during global configuration file creation that occurs when the
  configuration directory does not exist.

## 1.6.0 - 2019-05-07

### Added

- [#46](https://github.com/phly/keep-a-changelog/pull/46) adds the `--provider-domain` option to each of the `entry:*` and `release`
  commands, and the corresponding `domain` key in `.keep-a-changelog.ini` files.
  The option can be used to specify a custom domain for your chosen provider; it
  will be used to determine which git remote to push tags to, to generate links
  for changelog entries, and to make API calls to the provider.

- [#44](https://github.com/phly/keep-a-changelog/pull/44) adds the command `bump:patch` as an alias to `bump`/`bump:bugfix`.

- [#33](https://github.com/phly/keep-a-changelog/pull/33) adds support for usage with the symfony/console 3.4 series.

- [#41](https://github.com/phly/keep-a-changelog/pull/41) adds the command `version:list`, which will list all versions and
  associated release dates from the changelog file.

- [#41](https://github.com/phly/keep-a-changelog/pull/41) adds the command `version:remove <version>`, which will remove the
  changelog entry for the provided version, if it exists.

- [#41](https://github.com/phly/keep-a-changelog/pull/41) adds the command `version:show <version>`, which will show the full
  release entry in the changelog for the provided version, along with its
  release date.

### Changed

- [#41](https://github.com/phly/keep-a-changelog/pull/41) aliases the `edit` command to `version:edit`.

- [#41](https://github.com/phly/keep-a-changelog/pull/41) adds an optional `<version>` argument to the `version:edit` command,
  allowing users to edit a specific release version entry.

### Deprecated

- [#41](https://github.com/phly/keep-a-changelog/pull/41) deprecates the `edit` command in favor of `version:edit`.

### Removed

- Nothing.

### Fixed

- [#45](https://github.com/phly/keep-a-changelog/pull/45) updates the `GitHub` provider such that it now verifies that a signed
  commit matching the release has been pushed before attempting to create a
  release.

- [#45](https://github.com/phly/keep-a-changelog/pull/45) updates the `release` command to no longer hard-code using "origin" as the
  remote, and to instead lookup the remote based on the provider and package.
  When multiple remotes match (which should not happen), it will prompt the user
  to choose one, or abort.

- [#43](https://github.com/phly/keep-a-changelog/pull/43) fixes the markup used to generate a link to a merge request when using
  GitLab as your provider. In that scenario, the markup `[!{merge number}]` will
  now be used instead of `[#{merge number}]`.

## 1.5.1 - 2018-11-08

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#32](https://github.com/phly/keep-a-changelog/pull/32) fixes the order of arguments to the `getToken()` method invoked within `ReleaseCommand`, resolving a syntax error.

- [#31](https://github.com/phly/keep-a-changelog/pull/31) fixes a syntax error in the `release` command.

- [#30](https://github.com/phly/keep-a-changelog/pull/30) fixes the `EntryCommand`, adding a missing `global` (`-g`) option.

## 1.5.0 - 2018-11-07

### Added

- [#24](https://github.com/phly/keep-a-changelog/pull/24) adds a GitLab repository provider.  You can specify the new provider via a
  new `--provider gitlab` option to either the `tag` or `release` commands.

- [#27](https://github.com/phly/keep-a-changelog/pull/27) adds the ability to create either a local or global config file containing
  both the preferred/default provider to use, and its associated token. Usage is
  `keep-a-changelog config`, optionally with a `--global` or `-g` flag. The
  command will then prompt you for the provider and token.

### Changed

- [#27](https://github.com/phly/keep-a-changelog/pull/27) modifies each of the `release` and `entry:*` commands to use the
  appropriate global or local configuration file, if found, to determine the
  provider and/or token to use; if either of the `--token` or `--provider`
  options are provided during invocation, those will override the values from
  configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.4.4 - 2018-04-26

### Added

- Nothing.

### Changed

- [#22](https://github.com/phly/keep-a-changelog/pull/22) modifies how PR links are generated in several ways:
  - If the provided package name does not result in a valid PR link, it raises an excepion.
  - If the package name discovered in the `composer.json` does not result in a valid PR link, it then
  - Probes the git remotes to find the first that results in a valid package link.
  In each case, it performs a `HEAD` request on the generated link to determine if it is
  valid, following redirects as encountered.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.4.3 - 2018-04-25

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#21](https://github.com/phly/keep-a-changelog/pull/21) fixes an issue with releasing that occurs when the token file
  contains any additional whitespace (such as a trailing EOL character), resolving problems
  with creating a release via the GitHub API.

## 1.4.2 - 2018-04-25

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes the exception message emitted when `new` is called and a changelog file already exists to
  properly mention the `--overwrite`, not the `--override`, option.

- [#20](https://github.com/phly/keep-a-changelog/pull/20) fixes detection of a changelog when only one changelog
  entry is present in the file.

## 1.4.1 - 2018-04-25

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#19](https://github.com/phly/keep-a-changelog/pull/19) fixes the `new` command to correctly detect the `--overwrite` option instead of the `--override` option.

## 1.4.0 - 2018-04-20

### Added

- [#16](https://github.com/phly/keep-a-changelog/pull/16) adds functionality to prevent an existing changelog
  from being overwritten by the `new` command, and adds an `--overwrite` option
  to allow overwriting the file.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.0 - 2018-04-16

### Added

- [#14](https://github.com/phly/keep-a-changelog/pull/14) adds a new global option, `--file` (or `-f`), to allow specifying
  an alternate changelog file to create or modify.

- [#12](https://github.com/phly/keep-a-changelog/pull/12) adds a new command, "bump:to-version". This command will add a new changelog
  entry for the version specified on the command line at the top of the
  changelog file.

- [#11](https://github.com/phly/keep-a-changelog/pull/11) Adds a new command, "new", for creating a new changelog file. The file
  will be created in CHANGELOG.md in the current directory unless a --file
  option is provided. The initial version will be 0.1.0 unless an --initial-version
  option is provided.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.1 - 2018-04-15

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes a problem with the edit command successful message template, switching from
  `<success>` (which does not exist) to `<info>`.

- [#10](https://github.com/phly/keep-a-changelog/pull/10) adds a dependency on ocramius/package-versions in order to ensure that the
  script version is auto-updated for each tag.

## 1.2.0 - 2018-04-14

### Added

- Adds a new command, "edit", which will open the most recent changelog entry
  in an editor. By default, it uses `$EDITOR`, unless an alternate editor
  is provided via the `--editor` option. It will edit the file `CHANGELOG.md`
  unless an alternate file is provided via the `--file` option.

- Adds a new command, "ready", which will set the date for the first
  un-dated changelog entry in the CHANGELOG.md. You may also pass the --date or -d option
  to specify an alternate date, or to use a date formate other than YYYY-MM-DD.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.2 - 2018-04-12

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Sets the release version for the tool, bumping it to 1.1.2 from 1.0.3dev1.

## 1.1.1 - 2018-04-12

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes the dev-master branch alias in the package definition to ensure the package validates.

## 1.1.0 - 2018-04-12

### Added

- Adds the command "entry:fixed", for adding an entry to the Fixed section of the current changelog.

- Adds the command "entry:removed", for adding an entry to the Removed section of the current changelog.

- Adds the command "entry:deprecated", for adding an entry to the Deprecated section of the current changelog.

- Adds the command "entry:changed", for adding an entry to the Changed section of the current changelog.

- Adds the command "entry:added", for adding an entry to the Added section of the current changelog.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.2 - 2018-03-27

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes an autoloading issue when running globally.

## 1.0.1 - 2018-03-27

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Removes dependency on ocramius/package-versions.

### Fixed

- Fixes error that occurs when running globally, due to a dependency that only
  works when running in a local package.

## 1.0.0 - 2018-03-27

Initial release.

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
