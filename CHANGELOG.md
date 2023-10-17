# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Added a freshness filter (opposing the aging filter).
- Added extra functions to the main page: 'Mark Done Yesterday' and 'Delete'.
- Count of items shown / open on the screen version of the list. [#9]
- Half a donut chart showing items done per week. [#3]
- Support for showing deleted items.

### Changed

- Added logger.
- Force page size to US Letter.
- Hide filters when no items are open.
- Hide stats when no items have been closed.
- JavaScript and CSS are now served up via files compiled through Webpack. [#4]
- Moved the legacy view-like classes to App\Legacy\Renderer.
- Refactored display controls into their own class.
- Replaced custom date calculation class with Carbon.
- Replaced direct SQL queries with Doctrine wrappers and migrations. [#5]
- Replaced legacy database and entity objects with Doctrine versions.
- Replaced table tags with divs on the done pages. [#7]
- Switched from a custom session handler to the default PHP handler.
- Updated 3rd party Javascript dependencies. #4
- Updated 3rd party PHP dependencies.
  - Upgraded Symfony to 5.4.
- When adding an item, if a section filter has been set, it prepopulates the
  Section dropdown.

### Fixed

- A random string is no longer accepted as the range for the history page.
- Rendering of index page in mobile view. [#2]
- The edit, prioritize, and mark done buttons would be grayed out if all of the
  items on the page were closed.
- When editing items, setting the status will affect the completed timestamp.

### Security

- Users can no longer edit tasks from other users.

## [1.3.0] - 2020-12-22

### Added

- Bootstrap

### Changed

- Minimum PHP version is now 7.4.3.
- Moved logout to its own page.
- Replaced `config.php` with symfony/dotenv.
- Replaced inline HTML generation with twig templates.
- Replaced separate "printable" page with CSS-based controls on the index page.
- Replaced table tags, and the complicated splitting logic, with divs and CSS
  column controls
- The two "Show Done" pages are now a single page with a different view.
- Timezones are now based on modern names.
- Timezones are now set using modern methods.

### Removed

- Abhorrent admin functionality.
- Estimation support
- Export functionality
- The remnants of the recurring item functionality.

### Security

- Passwords are now hashed using modern methods.

### Fixed

- A bug that stopped users from being able to set their timezone.
- Clicking Edit without selecting an item no longer shows a blank page.
- Items shown / not shown on the printable sheet no longer includes completed
  items.
- Explicitly bring in global variables on pages.
- Sections with no open items were not getting rendered even if closed items
  were present, and the view requested them.

## [1.2.0] - 2020-09-03

### Changed

- Moved `public-old/` to `public/`.
- Updated the software to work under a modern version of PHP.
  - Removed calls to `stripslashes()`.
  - Removed PHP 4 logic.

### Removed

- Removed quick email export functionality.
- Removed user-controlled stylesheets.

## [1.1.0] - 2020-01-16

### Changed

- Updated the software to work under a modern version of PHP.
  - Add an autoloader, namespaces, little things.
  - Removed deprecated functions and features.

### Fixed

- An injection attack on the login page, in the laziest way possible.

## [1.0.0] - 2007-10-03

### Notes

- This was my todo list software for years.  It was quickly thrown together and
  then hacked as needed.  It has some interesting features, and I brought it
  back to life, so I could port it to a modern framework.
- It ran on a local machine, and I cannot recommend running it on a production
  machine in this state.  It was also meant to work with `gpc_magic_quotes`;
  without that option, it is vulnerable to injection attacks.

[Unreleased]: https://github.com/dharple/todo/compare/v1.3.0...main
[1.3.0]: https://github.com/dharple/todo/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/dharple/todo/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/dharple/todo/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/dharple/todo/releases/tag/v1.0.0

[#7]: https://github.com/dharple/todo/issues/7
[#5]: https://github.com/dharple/todo/issues/5
[#4]: https://github.com/dharple/todo/issues/4
[#3]: https://github.com/dharple/todo/issues/3
[#2]: https://github.com/dharple/todo/issues/2
