# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Legacy PHP to-do list web application (originally 2007) being incrementally modernized to Symfony 5.4. Doctrine ORM, Twig, and Symfony components are already in place; Symfony Controllers and full DI wiring are still in progress.

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

# Start dev server (legacy PHP built-in server)
php -S localhost:8000 -t public/ -d auto_prepend_file=include/common.php
```

## Tech Stack

- **PHP** 8.2, **Symfony** 5.4
- **Doctrine ORM** with migrations
- **Twig** templates, **Bootstrap** (index page; other pages pending)
- **Database**: MySQL, PostgreSQL, or SQLite

## Architecture

**Two-layer hybrid**: Active pages are plain PHP scripts in `public/` (account.php, item_edit.php, etc.). Symfony Controllers do not yet exist. The `public/` scripts rely on `include/common.php` being loaded as an auto-prepend, which boots the Symfony kernel, starts a PHP session, and authenticates the user via `App\Auth\Guard`.

**`App\Helper`** is a static service locator that bridges legacy code to the Symfony container (EntityManager, Twig, Logger). It's a deliberate temporary shim until DI is fully wired.

**Authentication** is session-based: `$_SESSION['userId']` stores the authenticated user ID. `App\Auth\Guard` handles login, password verification (`password_hash`/`password_verify`), and user resolution.

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
