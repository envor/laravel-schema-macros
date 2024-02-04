# laravel-schema-macros

[![Latest Version on Packagist](https://img.shields.io/packagist/v/envor/laravel-schema-macros.svg?style=flat-square)](https://packagist.org/packages/envor/laravel-schema-macros)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/envor/laravel-schema-macros/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/envor/laravel-schema-macros/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/envor/laravel-schema-macros/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/envor/laravel-schema-macros/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/envor/laravel-schema-macros.svg?style=flat-square)](https://packagist.org/packages/envor/laravel-schema-macros)

Some helpful (database level) macros for laravel's schema builder. Requires Laravel 11.

## Installation

You can install the package via composer:

```bash
composer require envor/laravel-schema-macros
```

## Usage

[databaseExists()](#databaseexists)    
[createDatabaseIfNotExists()](#createdatabaseifnotexists)   
[trashDatabase()](#trashdatabase)    
[emptyTrash()](#emptytrash)


### #`databaseExists()`

The `databaseExists()` method determines if the given database exists:

```php
use Illuminate\Support\Facades\Schema;

$database = database_path('my-new-database.sqlite');

Schema::connection('sqlite')->databaseExists($database);

// false

touch($database);

Schema::connection('sqlite')->databaseExists($database);

// true

Schema::connection('mysql')->databaseExists('abc');

// false

Schema::connection('mysql')->createDatabase('abc');


Schema::connection('mysql')->databaseExists('abc');

// true

```

### #`createDatabaseIfNotExists()`

The `createDatabaseIfNotExists()` method creates the given database if it does not exist:

```php
use Illuminate\Support\Facades\Schema;

$default = database_path('database.sqlite');

touch($default);

Schema::connection('sqlite')->createDatabaseIfNotExists($default);

// false

Schema::connection('sqlite')->createDatabaseIfNotExists(database_path('another_database'));

// true


Schema::connection('mysql')->createDatabase('brand_new_database');

// true

```

The `createDatabaseIfNotExists()` method will also create `sqlite` database files recursively:

```php

$newFile = database_path('/new/directories/will/be/created/recursively/db.sqlite');

Schema::connection('sqlite')->createDatabaseIfNotExists($newFile);

// true
```

### #`trashDatabase()`

The `trashDatabase()` method will move the database to the `trash` and timestamp it:

> [!TIP]
> Sqlite databases are moved to a `.trash` directory on the local storage disk by default.    
> You may optionally pass the name of another storage disk as a second argument.

```php
$database = database_path('database.sqlite');

Schema::connection('sqlite')->trashDatabase($database);

// /home/forge/mysite.com/storage/app/.trash/2024-02-04_06-29-11_database.sqlite

Schema::connection('mariadb')->trashDatabase('schema_demo');

// trashed_2024-02-04_06-44-42_schema_demo
```

### #`emptyTrash()`

The `emptyTrash()` method will erase all `trashed` databases from disk which are reachable from the current connection:

> [!TIP]
> To only permanently erase databases trashed later than a given age and keep those which are newer,    
> you may pass the maximum age in days for the databases you want to keep. 

```php
$database = database_path('database.sqlite');

Schema::connection('sqlite')->trashDatabase($database);

// /home/forge/mysite.com/storage/app/.trash/2024-02-04_06-29-11_database.sqlite

Schema::connection('sqlite')->emptyTrash();

// 1

Schema::connection('mysql')->trashDatabase('schema_demo');

// trashed_2024-02-04_06-44-42_schema_demo

Schema::connection('mysql')->emptyTrash();

// 1
```

## Testing

> [!IMPORTANT]  
> Tests use [spatie/docker](https://github.com/spatie/docker) for testing against various database servers.   
> Docker is required for running tests locally!

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [inmanturbo](https://github.com/envor)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
