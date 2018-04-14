# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.3.0 - TBD

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

## 1.2.1 - TBD

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
