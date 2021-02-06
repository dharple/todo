# Overview

A legacy web application for keeping track of todo lists.

This was originally written in ~2007.  (First commit was made on a baz repo, in 2007-09-10.)  I used it for a few years, but eventually moved on to other software for tracking todo lists.  In 2020, I picked it back up again, and began the process of converting it to using modern frameworks.

So far, I've introduced:
- PSR-12 style code
- Bootstrap (Index Page)
- Doctrine ORM
- Merged Index and Print Templates
- Twig

The remaining pieces:
- Bootstrap (Other Pages)
- Symfony Controllers and Kernel
- Database Migrations
- Unit Tests

# Requirements

* PHP
* MySQL

# Getting Started

Run `composer install`.

Copy `.env` to `.env.local`.  Edit it and set the database parameters.

Load the contents of the `sql/` directory in to the database.

```bash
cat sql/*.sql | mysql -u DB_USER -p DB_NAME
```

Start the server:
```bash
composer go
```

Connect to http://localhost:8666 and log in.

The password set in `sql/02-data.sql` is `changeme`.

# Scripts

The `bin` directory has several scripts that require a `.mylogin.cnf` file.

```bash
mysql_config_editor set --login-path=todo-old --user=todo --password
```

