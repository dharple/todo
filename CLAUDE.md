# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Legacy PHP to-do list web application (originally 2007) modernized to Symfony 5.4. Doctrine ORM, Twig, Symfony Controllers, and full DI wiring are in place.

## Commands

```bash
composer install                          # install dependencies
composer test                             # run PHPUnit tests
composer phpcs                            # check code style (PSR-12 + outsanity ruleset)
composer phpcbf                           # auto-fix style issues
composer phpstan                          # static analysis (level 5, all paths)
vendor/bin/rector process                 # apply PHP 8.1 modernization rules
vendor/bin/phpunit --filter testMethod    # run a single test by name

bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
bin/console user:add <username>
bin/console user:password <username>

# Start dev server
composer go
# or: php -S localhost:8000 -t public/ public/index.php
```

## Tech Stack

- **PHP** 8.2, **Symfony** 5.4
- **Doctrine ORM** with migrations
- **Twig** templates, **Bootstrap**
- **Database**: MySQL, PostgreSQL, or SQLite

## Architecture

**Symfony Controllers**: All pages are handled by Symfony controllers in `src/Controller/`. The Symfony front controller at `public/index.php` handles all routing. Legacy `.php` URLs (e.g. `/login.php`) are kept as 301 redirects in `config/routes.yaml`.

**Authentication**: Symfony's `form_login` firewall against `App\Entity\User` (implements `UserInterface`). `App\Auth\Guard` is still used for password hashing/verification in `AccountController`.

**`App\Helper`** is a legacy static service locator — **do not use it in controllers**. It requires `$GLOBALS['kernel']` which is not set in the Symfony controller context. Use injected services instead.

**Timezone**: `App\EventSubscriber\TimezoneSubscriber` sets `date_default_timezone_set()` on every authenticated request (priority -10, after the security firewall).

**Session / DisplayConfig**: `App\Renderer\DisplayConfig` is stored in Symfony's session under the key `displayConfig`. Load and save it via `$request->getSession()->get/set('displayConfig', ...)`.

**Data model**: Three Doctrine entities — `User` owns `Section[]` and `Item[]`; each `Item` belongs to a `Section` and a `User`. Item status is a plain string column (`Open`, `Closed`, `Deleted`).

**Rendering**: `App\Renderer\BaseDisplay` → `ListDisplay` / `SectionDisplay` render Twig templates and expose `getOutput()` / `getOutputCount()`. `App\Analytics\*` runs DQL queries against closed items for stats (this week, last month, by year, etc.).

## Code Standards

- `declare(strict_types=1)` on every PHP file
- Copyright header block required on every new file (see existing files for exact format)
- PHPDoc required: class-level doc comment, `@param` with description, `@return` tag, properties in alphabetical order
- `src/Legacy/` (if created) has relaxed docblock rules per `phpcs.xml.dist`
- Run `composer phpcs` and `composer phpstan` before committing

## Environment

Copy `.env` to `.env.local` and set:
- `DATABASE_URL` — MySQL, PostgreSQL, or SQLite connection string
- `APP_SECRET` — random Symfony secret
- `APP_ENV` — `dev` for local development
