<?php

namespace SergiX44\Gradio\Client;

use GuzzleHttp\Client as Guzzle;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use SergiX44\Hydrator\Hydrator;
use SergiX44\Hydrator\HydratorInterface;
use WebSocket\Client as WebSocket;

abstract class RemoteClient
{
    private string $src;

    protected Guzzle $httpClient;

    protected HydratorInterface $hydrator;

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

        $this->hydrator = new Hydrator();

        $this->httpClient = new Guzzle([
            'base_uri' => str_replace('ws', 'http', $this->src),
            'headers' => [
                'User-Agent' => 'gradio_client_php/1.0',
            ],
        ]);
    }

    protected function get(string $uri, array $params = [], string $dto = null)
    {
        $response = $this->httpClient->get($uri, ['query' => $params]);

        return $this->parseResponse($response, $dto);
    }

    protected function post(string $uri, array $params = [], string $dto = null)
    {
        $response = $this->httpClient->post($uri, ['json' => $params]);

        return $this->parseResponse($response, $dto);
    }

    protected function ws(string $uri, array $options = []): WebSocket
    {
        return new WebSocket(str_replace('http', 'ws', $this->src).$uri, $options);
    }

    private function parseResponse(ResponseInterface $response, string $mapTo = null): mixed
    {
        $body = $response->getBody()->getContents();

        if ($mapTo !== null) {
            return $this->hydrator->hydrateWithJson($mapTo, $body);
        }

        return json_decode($body, flags: JSON_THROW_ON_ERROR);
    }
}
