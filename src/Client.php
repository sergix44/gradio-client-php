<?php

namespace SergiX44\Gradio;

use InvalidArgumentException;
use SergiX44\Gradio\Client\Endpoint;
use SergiX44\Gradio\Client\RemoteClient;
use SergiX44\Gradio\DTO\Config;
use SergiX44\Gradio\DTO\Result;
use SergiX44\Gradio\DTO\Websocket\Estimation;
use SergiX44\Gradio\DTO\Websocket\Message;
use SergiX44\Gradio\DTO\Websocket\ProcessCompleted;
use SergiX44\Gradio\DTO\Websocket\ProcessStarts;
use SergiX44\Gradio\DTO\Websocket\QueueFull;
use SergiX44\Gradio\DTO\Websocket\SendData;
use SergiX44\Gradio\DTO\Websocket\SendHash;
use SergiX44\Gradio\Event\Event;
use SergiX44\Gradio\Exception\GradioException;
use SergiX44\Gradio\Exception\QueueFullException;
use SergiX44\Gradio\Websocket\MessageType;

class Client extends RemoteClient
{
    private const HTTP_PREDICT = 'run/predict';

    private const WS_PREDICT = 'queue/join';

    private const HTTP_CONFIG = 'config';

    protected Config $config;

    private string $sessionHash;

    private array $endpoints = [];

    public function __construct(string $src, Config $config = null)
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
                count($dep['inputs'])
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

    public function predict(array $arguments, string $apiName = null, int $fnIndex = null): ?Result
    {
        if ($apiName === null && $fnIndex === null) {
            throw new InvalidArgumentException('You must provide an apiName or fnIndex');
        }

        $endpoint = $this->endpoints[$apiName ?? $fnIndex] ?? null;

        if ($endpoint === null) {
            throw new InvalidArgumentException('Endpoint not found');
        }

        if ($endpoint->argsCount !== count($arguments)) {
            throw new InvalidArgumentException('Invalid number of arguments');
        }

        return $this->submit($endpoint, $arguments);
    }

    private function submit(Endpoint $endpoint, array $arguments): ?Result
    {
        $payload = $this->preparePayload($arguments);
        $this->fireEvent(Event::SUBMIT, $payload);

        if ($endpoint->useWebsockets) {
            return $this->websocketLoop($endpoint, $payload);
        }

        return $this->post(self::HTTP_PREDICT, ['data' => $payload], Result::class);
    }

    private function preparePayload(array $arguments): array
    {
        return array_map(static function ($arg) {
            if (is_resource($arg)) {
                return base64_encode(stream_get_contents($arg));
            }
            return $arg;
        }, $arguments);
    }

    /**
     * @param  Endpoint  $endpoint
     * @param  array  $payload
     * @return Result|null
     * @throws GradioException
     * @throws QueueFullException
     * @throws \JsonException
     */
    private function websocketLoop(Endpoint $endpoint, array $payload): ?Result
    {
        $ws = $this->ws(self::WS_PREDICT);

        $message = null;
        while (true) {
            $data = $ws->receive();

            // why sometimes $data is null?
            if ($data === null) {
                continue;
            }

            $message = $this->hydrator->hydrateWithJson(Message::class, $data);

            if ($message instanceof SendHash) {
                $ws->sendJson([
                    'fn_index' => $endpoint->index,
                    'session_hash' => $this->sessionHash,
                ]);
            } elseif ($message instanceof QueueFull) {
                $this->fireEvent(Event::QUEUE_FULL, [$message]);
                $ws->close();
                throw new QueueFullException();
            } elseif ($message instanceof Estimation) {
                $this->fireEvent(Event::QUEUE_ESTIMATION, [$message]);
            } elseif ($message instanceof SendData) {
                $ws->sendJson([
                    'fn_index' => $endpoint->index,
                    'session_hash' => $this->sessionHash,
                    'data' => $payload,
                    'event_data' => null,
                ]);
            } elseif ($message instanceof ProcessCompleted) {
                break;
            } elseif ($message instanceof ProcessStarts) {
                //$this->fireEvent(Event::PROCESS_STARTS, [$message]);
            } else {
                throw new GradioException("'Unknown message type $data");
            }
        }

        $ws->close();

        return $message?->output;
    }
}
