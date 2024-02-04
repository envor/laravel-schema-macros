# This is my package laravel-schema-macros

[![Latest Version on Packagist](https://img.shields.io/packagist/v/envor/laravel-schema-macros.svg?style=flat-square)](https://packagist.org/packages/envor/laravel-schema-macros)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/envor/laravel-schema-macros/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/envor/laravel-schema-macros/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/envor/laravel-schema-macros/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/envor/laravel-schema-macros/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/envor/laravel-schema-macros.svg?style=flat-square)](https://packagist.org/packages/envor/laravel-schema-macros)

Some helpful (database level) macros for laravel's schema builder

## Installation

You can install the package via composer:

```bash
composer require envor/laravel-schema-macros
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-schema-macros-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-schema-macros-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-schema-macros-views"
```

## Usage

```php
$schemaMacros = new Envor\SchemaMacros();
echo $schemaMacros->echoPhrase('Hello, Envor!');
```

## Testing

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
