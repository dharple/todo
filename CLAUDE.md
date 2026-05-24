# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Legacy PHP to-do list web application (originally 2007) modernized to Laravel 13. Eloquent ORM, Blade templates, Laravel Controllers, and standard Laravel conventions are in place.

## Commands

```bash
composer install                          # install dependencies
composer test                             # run PHPUnit tests
composer phpcs                            # check code style (PSR-12 + outsanity ruleset)
composer phpcbf                           # auto-fix style issues
composer phpstan                          # static analysis (level 5, all paths)
vendor/bin/rector process                 # apply PHP 8.3 modernization rules
vendor/bin/phpunit --filter testMethod    # run a single test by name

php artisan migrate
php artisan user:add <username>
php artisan user:password <username>

# Start dev server
composer go
# or: php artisan serve
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
