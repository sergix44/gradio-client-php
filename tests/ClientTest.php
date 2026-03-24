<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use SergiX44\Gradio\Client;
use SergiX44\Gradio\DTO\Config;
use SergiX44\Gradio\DTO\Output;

function configJson(array $overrides = []): string
{
    return json_encode(array_merge([
        'version' => '3.40.0',
        'mode' => 'blocks',
        'dev_mode' => false,
        'analytics_enabled' => false,
        'components' => [],
        'css' => null,
        'title' => 'Gradio',
        'is_space' => true,
        'enable_queue' => false,
        'show_error' => false,
        'show_api' => true,
        'is_colab' => false,
        'stylesheets' => [],
        'root' => '',
        'protocol' => 'sse_v3',
        'dependencies' => [
            [
                'api_name' => 'predict',
                'queue' => false,
            ],
            [
                'api_name' => 'generate',
                'queue' => true,
            ],
        ],
    ], $overrides));
}

function apiInfoJson(): string
{
    return json_encode([
        'named_endpoints' => [
            '/predict' => [
                'parameters' => [
                    ['label' => 'Input', 'type' => 'string', 'component' => 'Textbox'],
                ],
                'returns' => [
                    ['label' => 'Output', 'type' => 'string', 'component' => 'Textbox'],
                ],
            ],
        ],
        'unnamed_endpoints' => [],
    ]);
}

function predictOutputJson(array $data = ['Hello, world!']): string
{
    return json_encode([
        'data' => $data,
        'is_generating' => false,
        'duration' => 0.5,
        'average_duration' => 0.4,
    ]);
}

function createClientWithMock(array $responses, ?string $hfToken = null, array &$history = []): Client
{
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($history));

    return new Client(
        'https://test-space.hf.space',
        hfToken: $hfToken,
        httpClientOptions: ['handler' => $handlerStack],
    );
}

it('constructs client and fetches config', function () {
    $client = createClientWithMock([
        new Response(200, [], configJson()),
    ]);

    expect($client)->toBeInstanceOf(Client::class);

    $config = $client->getConfig();
    expect($config)->toBeInstanceOf(Config::class)
        ->and($config->version)->toBe('3.40.0')
        ->and($config->protocol)->toBe('sse_v3')
        ->and($config->show_api)->toBeTrue()
        ->and($config->is_space)->toBeTrue();
});

it('constructs client with pre-supplied config', function () {
    $config = new Config();
    $config->version = '4.0.0';
    $config->dependencies = [];
    $config->protocol = 'sse_v3';

    $client = new Client(
        'https://test-space.hf.space',
        config: $config,
    );

    expect($client->getConfig()->version)->toBe('4.0.0');
});

it('throws on invalid src url', function () {
    new Client('not-a-valid-url');
})->throws(InvalidArgumentException::class);

it('sends authorization header when hf_token is set', function () {
    $history = [];

    $client = createClientWithMock([
        new Response(200, [], configJson()),
        new Response(200, [], apiInfoJson()),
    ], hfToken: 'hf_test_token_123', history: $history);

    $client->viewApi();

    expect($history)->toHaveCount(2);

    foreach ($history as $transaction) {
        $authHeader = $transaction['request']->getHeader('Authorization');
        expect($authHeader)->toHaveCount(1)
            ->and($authHeader[0])->toBe('Bearer hf_test_token_123');
    }
});

it('does not send authorization header when hf_token is null', function () {
    $history = [];

    $client = createClientWithMock([
        new Response(200, [], configJson()),
        new Response(200, [], apiInfoJson()),
    ], history: $history);

    $client->viewApi();

    foreach ($history as $transaction) {
        $authHeader = $transaction['request']->getHeader('Authorization');
        expect($authHeader)->toBeEmpty();
    }
});

it('calls info endpoint and returns api info', function () {
    $history = [];

    $client = createClientWithMock([
        new Response(200, [], configJson()),
        new Response(200, [], apiInfoJson()),
    ], history: $history);

    $apiInfo = $client->viewApi();

    expect($apiInfo)->toBeArray()
        ->and($apiInfo)->toHaveKey('named_endpoints')
        ->and($apiInfo)->toHaveKey('unnamed_endpoints')
        ->and($apiInfo['named_endpoints'])->toHaveKey('/predict');

    $infoRequest = $history[1]['request'];
    expect((string) $infoRequest->getUri())->toContain('info');
    expect($infoRequest->getMethod())->toBe('GET');
});

it('predicts with api name and returns output', function () {
    $history = [];

    $client = createClientWithMock([
        new Response(200, [], configJson()),
        new Response(200, [], predictOutputJson(['result text'])),
    ], history: $history);

    $output = $client->predict(['hello'], apiName: 'predict');

    expect($output)->toBeInstanceOf(Output::class)
        ->and($output->getOutputs())->toBe(['result text'])
        ->and($output->getOutput(0))->toBe('result text');

    $predictRequest = $history[1]['request'];
    expect($predictRequest->getMethod())->toBe('POST')
        ->and((string) $predictRequest->getUri())->toContain('run/predict');

    $body = json_decode((string) $predictRequest->getBody(), true);
    expect($body['data'])->toBe(['hello'])
        ->and($body)->toHaveKey('fn_index')
        ->and($body)->toHaveKey('session_hash');
});

it('predicts with fn_index', function () {
    $client = createClientWithMock([
        new Response(200, [], configJson()),
        new Response(200, [], predictOutputJson(['fn result'])),
    ]);

    $output = $client->predict(['test'], fnIndex: 0);

    expect($output)->toBeInstanceOf(Output::class)
        ->and($output->getOutput(0))->toBe('fn result');
});

it('predicts with raw mode returns array', function () {
    $client = createClientWithMock([
        new Response(200, [], configJson()),
        new Response(200, [], predictOutputJson(['raw data'])),
    ]);

    $result = $client->predict(['test'], apiName: 'predict', raw: true);

    expect($result)->toBeArray()
        ->and($result['data'])->toBe(['raw data']);
});

it('throws when no api name or fn_index provided', function () {
    $client = createClientWithMock([
        new Response(200, [], configJson()),
    ]);

    $client->predict(['test']);
})->throws(InvalidArgumentException::class, 'You must provide an apiName or fnIndex');

it('throws when endpoint not found', function () {
    $client = createClientWithMock([
        new Response(200, [], configJson()),
    ]);

    $client->predict(['test'], apiName: '/nonexistent');
})->throws(InvalidArgumentException::class, 'Endpoint not found');

it('passes custom http client options', function () {
    $history = [];

    $mock = new MockHandler([
        new Response(200, [], configJson()),
        new Response(200, [], apiInfoJson()),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($history));

    $client = new Client(
        'https://test-space.hf.space',
        httpClientOptions: [
            'handler' => $handlerStack,
            'headers' => [
                'X-Custom-Header' => 'custom-value',
            ],
        ],
    );

    $client->viewApi();

    foreach ($history as $transaction) {
        $customHeader = $transaction['request']->getHeader('X-Custom-Header');
        expect($customHeader)->toHaveCount(1)
            ->and($customHeader[0])->toBe('custom-value');
    }
});

it('handles extra config properties via magic methods', function () {
    $config = new Config();
    $config->custom_property = 'test_value';

    expect($config->custom_property)->toBe('test_value')
        ->and(isset($config->custom_property))->toBeTrue()
        ->and($config->nonexistent)->toBeNull()
        ->and(isset($config->nonexistent))->toBeFalse();
});

it('returns outputs from output dto', function () {
    $output = new Output();
    $output->data = ['first', 'second', 'third'];

    expect($output->getOutputs())->toBe(['first', 'second', 'third'])
        ->and($output->getOutput(0))->toBe('first')
        ->and($output->getOutput(1))->toBe('second')
        ->and($output->getOutput(5))->toBeNull();
});

it('registers and fires event callbacks', function () {
    $fired = false;

    $client = createClientWithMock([
        new Response(200, [], configJson()),
        new Response(200, [], predictOutputJson()),
    ]);

    $client->onSubmit(function () use (&$fired) {
        $fired = true;
    });

    $client->predict(['test'], apiName: 'predict');

    expect($fired)->toBeTrue();
});

it('loads endpoints from config dependencies', function () {
    $client = createClientWithMock([
        new Response(200, [], configJson()),
    ]);

    $config = $client->getConfig();
    expect($config->dependencies)->toHaveCount(2);
});

it('preserves default headers when custom headers are provided', function () {
    $history = [];

    $mock = new MockHandler([
        new Response(200, [], configJson()),
        new Response(200, [], apiInfoJson()),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($history));

    $client = new Client(
        'https://test-space.hf.space',
        hfToken: 'hf_token_123',
        httpClientOptions: [
            'handler' => $handlerStack,
            'headers' => [
                'X-Custom' => 'value',
            ],
        ],
    );

    $client->viewApi();

    foreach ($history as $transaction) {
        $request = $transaction['request'];
        expect($request->getHeader('Authorization'))->toHaveCount(1)
            ->and($request->getHeader('Authorization')[0])->toBe('Bearer hf_token_123')
            ->and($request->getHeader('User-Agent'))->toHaveCount(1)
            ->and($request->getHeader('Accept'))->toHaveCount(1)
            ->and($request->getHeader('X-Custom'))->toHaveCount(1)
            ->and($request->getHeader('X-Custom')[0])->toBe('value');
    }
});

it('allows explicit authorization header override via client options', function () {
    $history = [];

    $mock = new MockHandler([
        new Response(200, [], configJson()),
        new Response(200, [], apiInfoJson()),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($history));

    $client = new Client(
        'https://test-space.hf.space',
        hfToken: 'hf_default_token',
        httpClientOptions: [
            'handler' => $handlerStack,
            'headers' => [
                'Authorization' => 'Bearer hf_override_token',
            ],
        ],
    );

    $client->viewApi();

    foreach ($history as $transaction) {
        $authHeader = $transaction['request']->getHeader('Authorization');
        expect($authHeader)->toHaveCount(1)
            ->and($authHeader[0])->toBe('Bearer hf_override_token');
    }
});
