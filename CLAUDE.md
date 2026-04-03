# CLAUDE.md

## Project Overview

Legacy PHP to-do list web application being modernized to Symfony 5.4. Originally written in 2007, now using Doctrine ORM, Twig, and Symfony components. In-progress migration from legacy code to Symfony controllers and unit tests.

## Tech Stack

- **PHP** 8.2, **Symfony** 5.4
- **Doctrine ORM** with migrations
- **Twig** templates, **Bootstrap** (index page; other pages pending)
- **Database**: MySQL, PostgreSQL, or SQLite

## Common Commands

```bash
# Install dependencies
composer install

# Run tests
composer test           # phpunit

# Code quality
composer phpcs          # check PSR-12 style
composer phpcbf         # auto-fix style issues
composer phpstan        # static analysis (level 5)

# Database
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate

# User management
bin/console user:add <username>
```

## Code Standards

- **Style**: PSR-12 via `outsanity/phpcs` ruleset — run `composer phpcs` before committing
- **Static analysis**: PHPStan level 5 — run `composer phpstan`
- `src/Legacy/` has relaxed docblock rules (ongoing migration)
- `declare(strict_types=1)` required on all new PHP files

## Project Structure

```
src/
  Analytics/    Controller/   Entity/    ORM/
  Auth/         Command/      Helper.php Repository/
  Kernel.php    Logger/       Renderer/
tests/          # PHPUnit tests (coverage target: src/)
migrations/     # Doctrine migrations
templates/      # Twig templates
config/         # Symfony config
public/         # Web root
```

## Environment Setup

Copy `.env` to `.env.local` and configure:
- `DATABASE_URL` — connection string for MySQL/PostgreSQL/SQLite
- `APP_SECRET` — random secret for Symfony
- `APP_ENV` — `dev` for local development

## Development Notes

- Migration in progress: legacy code lives in `src/Legacy/`, new Symfony controllers and unit tests are still being added
- Rector is configured (`rector.php`) for PHP 8.1 upgrades — run `vendor/bin/rector process` to apply modernization rules
- `var/` directory contains cache and logs; clear with `bin/console cache:clear`
