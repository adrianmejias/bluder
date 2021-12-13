# Blunder

[![security](https://github.com/adrianmejias/blunder/actions/workflows/security.yml/badge.svg)](https://github.com/adrianmejias/blunder/actions/workflows/security.yml) [![tests](https://github.com/adrianmejias/blunder/actions/workflows/tests.yml/badge.svg)](https://github.com/adrianmejias/blunder/actions/workflows/tests.yml) [![StyleCI](https://github.styleci.io/repos/394644917/shield?branch=main)](https://github.styleci.io/repos/394644917?branch=main) [![Build Status](https://travis-ci.com/adrianmejias/blunder.svg?branch=main)](https://travis-ci.com/adrianmejias/blunder) [![codecov](https://codecov.io/gh/adrianmejias/blunder/branch/main/graph/badge.svg?token=P087FQPJ65)](https://codecov.io/gh/adrianmejias/blunder) ![Downloads](https://img.shields.io/packagist/dt/adrianmejias/blunder) ![Packagist](https://img.shields.io/packagist/v/adrianmejias/blunder) ![License](https://img.shields.io/packagist/l/adrianmejias/blunder) ![Liberapay](https://img.shields.io/liberapay/patrons/adrianmejias.svg?logo=liberapay)

A PHP library that provides a pretty output for PHP error and exception handling.

## Installation

This version supports PHP 8.0. You can install the package via composer:

`composer require adrianmejias/blunder`

### Example
```php
<?php

require __DIR__ . '/vendor/autoload.php';

use AdrianMejias\Blunder\Blunder;

$blunder = new Blunder();
$blunder->register();

## Testing

`composer test`

## Todo

- [x] Add to packagist repo
- [x] Add unit tests
- [x] Add documentation for open source contributations
- [x] Add GitHub Action for unit tests
- [ ] Add more unit test coverages
- [ ] Add more documentation to README.md
- [ ] Add API listing to README.md

## Contributing

Thank you for considering contributing to Veil! You can read the contribution guide [here](.github/CONTRIBUTING.md).

## Code of Conduct

In order to ensure that the community is welcoming to all, please review and abide by the [Code of Conduct](.github/CODE_OF_CONDUCT.md).

## Security Vulnerabilities

Please see the [security file](SECURITY.md) for more information.

## License

The MIT License (MIT). Please see the [license file](LICENSE.md) for more information.
