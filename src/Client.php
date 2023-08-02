<?php

namespace SergiX44\Gradio;

use InvalidArgumentException;
use SergiX44\Gradio\Client\Endpoint;
use SergiX44\Gradio\Client\RemoteClient;
use SergiX44\Gradio\DTO\Config;

class Client extends RemoteClient
{

    private const HTTP_PREDICT = 'run/predict';

    private const WS_PREDICT = 'queue/join';
    private const HTTP_CONFIG = 'config';
    protected Config $config;
    private string $sessionHash;
    private array $endpoints = [];

    public function __construct(string $src, ?Config $config = null)
    {
        parent::__construct($src);
        $this->config = $config ?? $this->get(self::HTTP_CONFIG, dto: Config::class);
        $this->loadEndpoints($this->config->dependencies);
        $this->sessionHash = substr(md5(microtime()), 0, 11);
    }

    protected function loadEndpoints(array $dependencies): void
    {
        foreach ($dependencies as $index => $dep) {
            $endpoint = new Endpoint(
                $this,
                $index,
                $dep['api_name'] ?? null,
                $dep['queue'] !== false,
            );

            $this->endpoints[$index] = $endpoint;
            if ($endpoint->apiName !== null) {
                $this->endpoints[$endpoint->apiName] = $endpoint;
            }
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function predict(array $arguments, string $apiName = null, int $fnIndex = null): mixed
    {
        if ($apiName === null && $fnIndex === null) {
            throw new InvalidArgumentException('You must provide an apiName or fnIndex');
        }

        $endpoint = $this->endpoints[$apiName ?? $fnIndex] ?? null;

        if ($endpoint === null) {
            throw new InvalidArgumentException('Endpoint not found');
        }

        return $this->submit($endpoint, $arguments);
    }

    private function submit(Endpoint $endpoint, array $arguments)
    {
        $payload = $this->preparePayload($arguments);

        if ($endpoint->useWebsockets) {
            $ws = $this->ws(self::WS_PREDICT);
            $ws->send(json_encode($payload));
            $response = json_decode($ws->receive(), true);
            $ws->close();
        } else {
            $response = $this->post(self::HTTP_PREDICT, $payload);
        }
    }

}
