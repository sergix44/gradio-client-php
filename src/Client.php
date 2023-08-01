<?php

namespace SergiX44\Gradio;

use GuzzleHttp\Client as Guzzle;
use InvalidArgumentException;
use SergiX44\Gradio\DTO\Config;
use SergiX44\Hydrator\Hydrator;
use SergiX44\Hydrator\HydratorInterface;
use WebSocket\Client as WebSocket;

class Client
{
    private const HTTP_PREDICT = 'run/predict';

    private const WS_PREDICT = 'queue/join';

    private string $sessionHash;

    private Config $config;

    private Guzzle $httpClient;

    private WebSocket $wsClient;

    private HydratorInterface $hydrator;

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

        $src = str_ends_with($src, '/') ? $src : "{$src}/";
        $this->sessionHash = substr(md5(microtime()), 0, 11);
        $this->hydrator = new Hydrator();

        $this->httpClient = new Guzzle([
            'base_uri' => str_replace('ws', 'http', $src),
            'headers' => [
                'User-Agent' => 'gradio_client_php/1.0',
            ],
        ]);
        $this->wsClient = new WebSocket(str_replace('http', 'ws', $src));

        $this->config = $this->loadConfig();
    }

    public function predict(string $apiName = null, int $fnIndex = null, mixed ...$arguments)
    {
        if ($apiName === null && $fnIndex === null) {
            throw new InvalidArgumentException('You must provide an apiName or fnIndex');
        }

        $fn = $fnIndex ?? $this->config->fnIndexFromApiName($apiName);

    }

    private function submit(int $dnIndex, mixed ...$arguments)
    {

    }

    private function loadConfig()
    {
        $response = $this->httpClient->get('config');

        return $this->hydrator->hydrateWithJson(Config::class, $response->getBody()->getContents());
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
