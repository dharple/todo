# Overview

An *ancient* web application for keeping track of todo lists.

# Requirements

* PHP
* MySQL
* An old priest and a young priest

# Getting Started

Run `composer install`.

Copy `config.php.default` to `config.php`.  Edit it and set the database parameters.

Load the contents of the `sql/` directory in to the database.

```bash
cat sql/*.sql | mysql -u DB_USER -p DB_NAME
```

Start the server:
```bash
composer go-old
```

Connect to http://localhost:8666 and log in.

The password is in `sql/02-data.sql`, as are all of your worst fears.

