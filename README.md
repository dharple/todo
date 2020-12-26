# Overview

A legacy web application for keeping track of todo lists.

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

