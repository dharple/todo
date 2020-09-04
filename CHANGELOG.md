# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Changed
- Replaced inline HTML generation with twig templates.
- Replaced table tags, and the complicated splitting logic, with divs and CSS
  column controls

## [1.2.0] - 2020-09-03
### Changed
- Moved `public-old/` to `public/`.
- Updated the software to work under a modern version of PHP.
  - Removed calls to stripslashes().
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
  back to life so I could port it to a modern framework.
- It ran on a local machine, and I cannot recommend running it on a production
  machine in this state.  It was also meant to work with `gpc_magic_quotes`;
  without that option, it is vulnerable to injection attacks.

[Unreleased]: https://github.com/dharple/todo-old/compare/v1.2.0...master
[1.2.0]: https://github.com/dharple/todo-old/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/dharple/todo-old/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/dharple/todo-old/releases/tag/v1.0.0
