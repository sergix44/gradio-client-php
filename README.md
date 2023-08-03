# Gradio Client for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sergix44/gradio-client-php.svg?style=flat-square)](https://packagist.org/packages/sergix44/gradio-client-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/sergix44/gradio-client-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sergix44/gradio-client-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/sergix44/gradio-client-php.svg?style=flat-square)](https://packagist.org/packages/sergix44/gradio-client-php)

A PHP client to call Gradio APIs.

## TODO
- [x] HTTP and WS support
- [x] `predict`
- [ ] `upload`
- [ ] Event system
- [ ] Add tests
- [ ] Add more examples
- [ ] Add documentation

## Installation

You can install the package via composer:

```bash
composer require sergix44/gradio-client-php
```

## Usage

```php
use SergiX44\Gradio\Client;

$client = new Client('https://my-special.hf.space');

$result = $client->predict(['arg', 1, 2], apiName: 'myFunction');

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sergio Brighenti](https://github.com/SergiX44)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
