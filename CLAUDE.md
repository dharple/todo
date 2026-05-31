# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with
code in this repository.

## Project Overview

This is a Laravel web app, designed to keep track of a user's open tasks, and
to report on completed tasks.  Each user has their own set of tasks, and their
own set of sections, used to group the tasks.  This is the 3rd iteration of
this project.  It was written in 2007 with no framework, then converted to a
Symfony project, and finally converted to use Laravel.

## Commands

### Setup

```bash
composer install
./artisan key:generate
./artisan migrate
```

### Running

```bash
composer go     # Flush caches and start PHP dev server on localhost:8000
```

### Manage Users

```bash
./artisan user:add      username password
./artisan user:password username
```

### Cleanup Tools

```bash
./artisan items:closed:list
./artisan items:closed:purge
./artisan items:deleted:list
./artisan items:deleted:purge
```

### Database

```bash
./artisan migrate         # Apply migrations
./artisan migrate:fresh   # Drop and recreate database
```

### Dev Tools

```bash
# All of these commands can take one or more filenames or directories on the
# command line, to narrow the scope of their execution

composer phpstan    # Static Analysis
composer phpcs      # Check Code Style
composer phpcbf     # Fix Code Style
composer rector     # Show Opportunities for Automatic Refactoring
composer test       # Run unit tests

# Direct access tools

vendor/bin/rector   # Do Automatic Refactoring
vendor/bin/phpunit --filter <pattern> # Run unit tests matching a given pattern
```

## Tech Stack

- **PHP** 8.3, **Laravel** 13
- **Eloquent ORM** with migrations
- **Blade** templates, **Bootstrap** 5
- **Database**: MySQL, PostgreSQL, or SQLite

## Architecture

**Laravel Controllers**: All pages are handled by controllers in `app/Http/Controllers/`. Routes are defined in `routes/web.php`.

**Authentication**: Laravel's standard `Auth::attempt()` with the `web` guard against `App\Models\User`. The User model overrides `getAuthIdentifierName()` to return `'username'` instead of `'email'`. `App\Services\Guard` handles password hashing/verification.

**Timezone**: `App\Http\Middleware\SetTimezone` sets `date_default_timezone_set()` on every authenticated request via the web middleware stack (registered in `bootstrap/app.php`).

**Session / DisplayConfig**: `App\Renderer\DisplayConfig` is stored in Laravel's session under the key `displayConfig`. Load and save it via `session('displayConfig')` / `session(['displayConfig' => $config])`.

**Data model**: Three Eloquent models — `User` owns `Section[]` and `Item[]`; each `Item` belongs to a `Section` and a `User`. Item status is a plain string column (`Open`, `Closed`, `Deleted`). No `created_at`/`updated_at` timestamp columns — `$timestamps = false` on all models.

**Rendering**: `App\Renderer\BaseDisplay` → `ListDisplay` / `SectionDisplay` render Blade templates and expose `getOutput()` / `getOutputCount()`. `App\Analytics\*` runs Eloquent queries against closed items for stats (this week, last month, by year, etc.).

## Code Standards

- `declare(strict_types=1)` on every PHP file
- Copyright header block required on every new file (see existing files for exact format)
- PHPDoc required: class-level doc comment, `@param` with description, `@return` tag, properties in alphabetical order
- Run `composer phpcs` and `composer phpstan` before committing

## Environment

Copy `.env.example` to `.env` and set:
- `DB_CONNECTION` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD`
- `APP_KEY` — generate with `php artisan key:generate`
- `APP_ENV` — `local` for local development
