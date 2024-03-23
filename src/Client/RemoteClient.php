<?php

namespace SergiX44\Gradio\Client;

use GuzzleHttp\Client as Guzzle;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use SergiX44\Gradio\Event\EnhancedClient;
use SergiX44\Hydrator\Hydrator;
use SergiX44\Hydrator\HydratorInterface;

abstract class RemoteClient extends RegisterEvents
{
    private string $src;

    protected Guzzle $httpClient;

    protected HydratorInterface $hydrator;

    public function __construct(string $src, array $httpClientOptions = [])
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

        $this->httpClient = new Guzzle(array_merge([
            'base_uri' => str_replace('ws', 'http', $this->src),
            'headers' => [
                'User-Agent' => 'gradio_client_php/1.0',
                'Accept' => 'application/json',
            ],
        ], $httpClientOptions));
    }

    protected function http(string $method, string $uri, array $params = [], array $opt = [], ?string $dto = null)
    {
        $response = $this->httpRaw($method, $uri, $params, $opt);

        return $this->decodeResponse($response, $dto);
    }

    protected function httpRaw(string $method, string $uri, array $params = [], array $opt = [])
    {
        $keyContent = $method === 'get' ? 'query' : 'json';

        return $this->httpClient->request($method, $uri, array_merge([
            $keyContent => $params,
        ], $opt));
    }

    protected function ws(string $uri, array $options = []): EnhancedClient
    {
        return new EnhancedClient(str_replace('http', 'ws', $this->src).$uri, $options);
    }

    protected function decodeResponse(ResponseInterface|string $response, ?string $mapTo = null): mixed
    {
        $body = $response instanceof ResponseInterface ? $response->getBody()->getContents() : $response;

        if ($mapTo !== null) {
            return $this->hydrator->hydrateWithJson($mapTo, $body);
        }

        return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
    }
}
