# Changelog

## v1.1.0 - 05/03/2017

### Added
* Added locallib file to manage preparation of notifications to be rendered.
* Added CHANGELOG.md file to start keeping track of changes.

### Changed
* Moved database calls out of renderer function 'render_notification' - it now purely renders the notifications.
* PHPDocs corrections for class 'advnotifications'.
* Updated 'Message' field to be texarea - allowing for resizing of field.
* Updated SCSS (and therefore CSS) to support resizing of textarea & improved UX for dismiss/close button.
* Bumped version number.