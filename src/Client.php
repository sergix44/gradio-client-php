<?php

namespace SergiX44\Gradio;

use GuzzleHttp\Client as Guzzle;
use InvalidArgumentException;
use WebSocket\Client as WebSocket;

class Client
{
    private const HTTP_PREDICT = 'run/predict';

    private const WS_PREDICT = 'queue/join';

    private string $src;

    private string $sessionHash;

    private array $config = [];

    private Guzzle $httpClient;

    private WebSocket $wsClient;

    public function __construct(string $src)
    {
        if (
            ! str_starts_with($src, 'http://') &&
            ! str_starts_with($src, 'https://') &&
            ! str_starts_with($src, 'ws://') &&
            ! str_starts_with($src, 'wss://')
        ) {
            throw new InvalidArgumentException('The src must not contain the protocol');
        }

        $this->src = str_ends_with($src, '/') ? $src : "{$src}/";
        $this->sessionHash = substr(md5(microtime()), 0, 11);

        $this->httpClient = new Guzzle([
            'base_uri' => str_replace('ws', 'http', $this->src),
            'headers' => [
                'User-Agent' => 'gradio_client_php/1.0',
            ],
        ]);
        $this->wsClient = new WebSocket(str_replace('http', 'ws', $this->src));

        $this->config = $this->getConfig();
    }

    public function predict(string $apiName = null, int $fnIndex = null, mixed ...$arguments)
    {

    }

    private function submit()
    {

    }

    private function getConfig()
    {
        $response = $this->httpClient->get('config');

        return json_decode($response->getBody()->getContents(), flags: JSON_THROW_ON_ERROR);
    }
}
