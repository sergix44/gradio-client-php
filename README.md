# Gradio Client for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sergix44/gradio-client-php.svg?style=flat-square)](https://packagist.org/packages/sergix44/gradio-client-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/sergix44/gradio-client-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sergix44/gradio-client-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/sergix44/gradio-client-php.svg?style=flat-square)](https://packagist.org/packages/sergix44/gradio-client-php)

A PHP client to call [Gradio](https://www.gradio.app) APIs.

## TODO
- [x] HTTP and WS support
- [x] `predict`
- [x] getConfig
- [x] client options
- [x] hf_token support
- [x] viewApi
- [x] Finish event system
- [x] Add tests

## Installation

You can install the package via composer:

```bash
composer require sergix44/gradio-client-php
```

## Usage

### Basic Usage

```php
use SergiX44\Gradio\Client;

$client = new Client('https://my-special.hf.space');

$result = $client->predict(['arg', 1, 2], apiName: 'myFunction');

$outputs = $result->getOutputs(); // returns array of all outputs
$first = $result->getOutput(0);   // returns a single output by index
```

### Hugging Face Token Authentication

To access private Hugging Face Spaces, pass your HF token:

```php
use SergiX44\Gradio\Client;

$client = new Client('https://my-private.hf.space', hfToken: 'hf_your_token_here');

$result = $client->predict(['hello'], apiName: 'chat');
```

The token is automatically sent as a Bearer token in the `Authorization` header on all HTTP and WebSocket requests.

### Viewing Available API Endpoints

Use `viewApi()` to inspect the available API endpoints, their parameters, and return types:

```php
use SergiX44\Gradio\Client;

$client = new Client('https://my-special.hf.space');

$apiInfo = $client->viewApi();

// Returns an array with 'named_endpoints' and 'unnamed_endpoints'
// Each endpoint includes parameter and return type information
print_r($apiInfo);
```

### Client Options

You can pass custom HTTP client options (Guzzle options) to customize the underlying HTTP client:

```php
use SergiX44\Gradio\Client;

$client = new Client('https://my-special.hf.space', httpClientOptions: [
    'timeout' => 30,
    'headers' => [
        'X-Custom-Header' => 'value',
    ],
    'proxy' => 'http://proxy.example.com:8080',
]);
```

### Using Function Index

If you know the function index instead of the API name, you can use `fnIndex`:

```php
$result = $client->predict(['input'], fnIndex: 0);
```

### Raw Response

To get the raw decoded response instead of an `Output` DTO:

```php
$result = $client->predict(['input'], apiName: 'myFunction', raw: true);
// $result is an associative array
```

### Event System

You can register callbacks for various events during the prediction process:

```php
use SergiX44\Gradio\Client;
use SergiX44\Gradio\DTO\Messages\Estimation;
use SergiX44\Gradio\DTO\Messages\ProcessCompleted;

$client = new Client('https://my-special.hf.space');

// Called when a prediction is submitted
$client->onSubmit(function (array $payload) {
    echo "Submitted!\n";
});

// Called when queue position is estimated
$client->onQueueEstimation(function (Estimation $estimation) {
    echo "Queue position: {$estimation->rank}\n";
});

// Called when processing starts
$client->onProcessStarts(function () {
    echo "Processing started\n";
});

// Called when processing completes (success or failure)
$client->onProcessCompleted(function (ProcessCompleted $message) {
    echo "Completed: " . ($message->success ? 'success' : 'failed') . "\n";
});

// Called only on success
$client->onProcessSuccess(function (ProcessCompleted $message) {
    $output = $message->output;
});

// Called only on failure
$client->onProcessFailed(function (ProcessCompleted $message) {
    echo "Failed!\n";
});

// Called when the queue is full
$client->onQueueFull(function () {
    echo "Queue is full!\n";
});

// Called during streaming/generating
$client->onProcessGenerating(function () {
    echo "Generating...\n";
});

$result = $client->predict(['input'], apiName: 'myFunction');
```

### Accessing Config

```php
$config = $client->getConfig();

echo $config->version;   // Gradio version
echo $config->protocol;  // 'sse_v3', 'ws', etc.
echo $config->title;     // App title
```

### File Upload

You can pass file paths or resources as arguments, and they will be automatically encoded as base64:

```php
// Using a file path
$result = $client->predict(['/path/to/image.png'], apiName: 'classify');

// Using a resource
$stream = fopen('/path/to/audio.mp3', 'r');
$result = $client->predict([$stream], apiName: 'transcribe');
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
