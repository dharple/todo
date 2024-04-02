# Overview

A legacy web application for keeping track of to do lists.

This was originally written in ~2007.  (The first commit was made on a baz
repo, in 2007-09-10.)  I used it for a few years, but eventually moved on to
other software for tracking to do lists.  In 2020, I picked it back up again,
and began the process of converting it to using modern frameworks.

So far, I've introduced:
- PSR-12 style code
- Bootstrap (Index Page)
- Doctrine ORM
- Dotenv
- Merged Index and Print Templates
- Symfony Kernel
- Twig
- Webpack

The remaining pieces:
- Bootstrap (Other Pages)
- Symfony Controllers
- Database Migrations
- Unit Tests

# Requirements

* Composer
* MySQL, PostgreSQL or SQLite
* Node
* PHP

# Getting Started

## Install 3rd Party Dependencies:

```bash
composer install
npm install
npm run prod
```

## Set up .env

Copy `.env` to `.env.local`.  Edit it and set the database parameters.

## Generate DB Schema

Create the database using:

```bash
bin/console doctrine:database:create
```

Update the schema after updates using:

```bash
bin/console doctrine:migrations:migrate
```

## Create a User

```bash
bin/console user:add myuser
```

## Test

Start the server:
```bash
composer go
```

Connect to http://localhost:8666 and log in.

# Thanks

Favicon courtesy of [favicon.ico].

Doughnut colors courtesy of [Manish] @ [SchemeColor].

[favicon.ico]: https://favicon.io/emoji-favicons/cherry-blossom
[Manish]: https://www.schemecolor.com/author/manish
[SchemeColor]: https://www.schemecolor.com/retro-orange-and-blue.php
