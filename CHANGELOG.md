# Changelog

## v1.3.9 - 22/06/2020

### Changed
* Travis file updated to Moodle 3.9.
* Updated buttons classes to be more consistent - primary, secondary, light.
* Bumped version number.

## v1.3.8 - 29/04/2020

### Changed
* Update imagery - video and gif.
* Simplify README.
* Bumped version number.

### Fixed
* Announcement alert type now works properly for preview alert - allowing live preview if custom CSS exists for announcement type alert.

## v1.3.7 - 19/12/2019

### Changed
* Travis file updated to Moodle 3.8.
* Bumped version number.

## v1.3.6 - 19/09/2019

### Changed
* Code style fixes.
* Bumped version number.

## v1.3.5 - 17/09/2019

### Changed
* Moved JS to AMD format.
* Updated TravisCI file to Ubuntu Xenial - MDL-65992.
* Bumped version number.

## v1.3.4 - 11/07/2019

### Changed
* Removed redundant global variable.
* Bumped version number.

### Fixed
* Use lang string for notification type in table(s).

## v1.3.3 - 11/07/2019

### Added
* Support for theme to override images - thanks @amandadoughty.

### Changed
* 'Message' field width enlarged.
* Minor CSS updates.
* Bumped version number.

### Fixed
* Placeholder for date fields updated to expected format - for browsers that don't support 'date' input types.

## v1.3.2 - 23/01/2019

### Changed
* TravisCI file update - ci tool version/source.
* README tweak.
* Bumped version number.

### Fixed
* Squashed regression bug - filter removed data attributes required for AJAX calls to dismiss notifications.
* Seen count SQL - when user exports data "Yes" or "No" was shown instead of the seen count.
* Only show manage-related links if the user has permission.

## v1.3.1 - 21/01/2019

### Changed
* TravisCI file update.
* Bumped version number.

### Fixed
* Minor code style fixes.
* EOL fixes.

## v1.3.0 - 21/01/2019

### Added
* Support for Privacy API (GDPR Compliance).
* Language strings for Privacy API.

### Changed
* Bumped version number.

## v1.2.4 - 18/07/2018

### Added
* Support added for multilang (and other) filters.

### Changed
* Bumped version number.

## v1.2.3 - 13/07/2018

### Changed
* If the from/to dates are not set, a `-` is now displayed instead of 01/01/1970.
* If the from/to dates match the 'to date' is displayed as a `-`.
* Updated README - HTML allowed since previous update.
* Bumped version number.

## v1.2.2 - 05/07/2018

### Added
* 'Allow HTML' setting - toggles if basic HTML is allowed in the Title and Message (filtered for scripts, etc).

### Changed
* Preview & Form-related JavaScript improved - more robust and responsive UI/UX.
* Minor styling update for status indicator (Saving/Done).
* Bumped version number.

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
* Fixed minor illogical JS error.

## v1.1.1 - 12/03/2018

### Changed
* Small UX Improvement - better padding for dismiss button.
* Bumped version number.

### Fixed
* Fixed bug that caused illogical redirect(s).

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