# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tafhyseni/php-multi-tenancy.svg?style=flat-square)](https://packagist.org/packages/tafhyseni/php-multi-tenancy)
[![Build Status](https://img.shields.io/travis/tafhyseni/php-multi-tenancy/master.svg?style=flat-square)](https://travis-ci.org/tafhyseni/php-multi-tenancy)
[![Quality Score](https://img.shields.io/scrutinizer/g/tafhyseni/php-multi-tenancy.svg?style=flat-square)](https://scrutinizer-ci.com/g/tafhyseni/php-multi-tenancy)
[![Total Downloads](https://img.shields.io/packagist/dt/tafhyseni/php-multi-tenancy.svg?style=flat-square)](https://packagist.org/packages/tafhyseni/php-multi-tenancy)

Simple PHP package to help you control, create and manage Tenancy architectural databases. 

Currently this works only on MySQL, but other databases will be added shortly! 
Also, this does work only on same hostname databases, it will also soon extended to support different hostnames..

Want to contribute.. you're welcome :)

## Installation

You can install the package via composer:

```bash
composer require tafhyseni/php-multi-tenancy
```

## Initialization
Initialization is simple as long as you do not forget to pass configuration properly

``` php
$tenancy = new Tenancy(
    array(
        'hostname' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'database' => 'test',
        'tenancy_hostname' => '127.0.0.1',
        'tenancy_username' => 'root',
        'tenancy_password' => ''
    )
);
```

## Usage
Generating an entire tenancy schema with all tables and data

``` php
$name = $tenancy->generate(NULL, array(), true);
```

Generating an entire tenancy schema with only some tables and data 

``` php
$name = $tenancy->generate(NULL, array('table_to_clone_1', 'table_to_clone_2'), true);
```

Generating an entire tenancy schema with only some tables, no data and specified tenancy name 

``` php
$name = $tenancy->generate('my_tenancy_db', array('table_to_clone_1'), data);

```
### Testing

``` bash
composer require --dev phpunit/phpunit
./vendor/bin/phpunit tests/TenancyTest
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email tafhyseni@gmail.com instead of using the issue tracker.

## Credits

- [Mustafe Hyseni](https://github.com/tafhyseni)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## PHP Package Boilerplate

This package was generated using the [PHP Package Boilerplate](https://laravelpackageboilerplate.com).
