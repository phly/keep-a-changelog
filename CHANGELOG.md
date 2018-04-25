# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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

## 1.1.3 - TBD

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
