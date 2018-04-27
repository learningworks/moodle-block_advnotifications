# Changelog

## v1.2.1 - 27/04/2018

### Added
* Added .stylelintignore file to exclude scss files (only check final css file).

### Changed
* Bumped version number.

## v1.2.0 - 24/04/2018

### Added
* Added breadcrumbs to manage & restore pages.
* Added language strings for breadcrumbs.

### Changed
* Updated dates in Changelog (typos).
* Bumped version number.

### Fixed
* Fixed minor illogical JS error - Issue #8.

## v1.1.1 - 12/03/2018

### Changed
* Small UX Improvement - better padding for dismiss button.
* Bumped version number.

### Fixed
* Fixed bug that caused illogical redirect(s) - Issue #7.

## v1.1.0 - 05/03/2018

### Added
* Added locallib file to manage preparation of notifications to be rendered.
* Added CHANGELOG.md file to start keeping track of changes.

### Changed
* Moved database calls out of renderer function 'render_notification' - it now purely renders the notifications.
* PHPDocs corrections for class 'advnotifications'.
* Updated 'Message' field to be texarea - allowing for resizing of field.
* Updated SCSS (and therefore CSS) to support resizing of textarea & improved UX for dismiss/close button.
* Bumped version number.