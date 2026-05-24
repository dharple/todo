# Overview

A legacy web application for keeping track of to do lists, slowly being
modernized.

This was originally written in ~2007.  In 2020, I picked it back up again,
and began the process of converting it to using modern frameworks.

# Upgrading

Todo v3 introduces a new framework, Laravel, and a shift in the .env format.
If you had previously been running Todo on v2, move your .env.local out of the
way.  Update as usual, then copy `.env.example` to `.env`, and update the
database settings, using your previous copy of `.env.local` as a guide.

# Requirements

* Composer
* MySQL, PostgreSQL or SQLite
* PHP

# Getting Started

## Install 3rd Party Dependencies:

```bash
composer install
```

## Set up .env

Copy `.env.example` to `.env`.  Edit it and set the database parameters.

## Geneate any application encryption key

```bash
./artisan key:generate
```

## Generate DB Schema

Create the database using, or update the schema after changes using:

```bash
./artisan migrate
```

## Create a User

```bash
./artisan user:add
```

## Test

Start the server:
```bash
composer go
```

Connect to http://localhost:8000 and log in.

# Thanks

Favicon courtesy of [favicon.ico].

Doughnut colors courtesy of [Manish] @ [SchemeColor].

[favicon.ico]: https://favicon.io/emoji-favicons/cherry-blossom
[Manish]: https://www.schemecolor.com/author/manish
[SchemeColor]: https://www.schemecolor.com/retro-orange-and-blue.php
