<?php

namespace SergiX44\Gradio;

use InvalidArgumentException;
use SergiX44\Gradio\Client\Endpoint;
use SergiX44\Gradio\Client\RemoteClient;
use SergiX44\Gradio\DTO\Config;
use SergiX44\Gradio\DTO\Output;
use SergiX44\Gradio\DTO\Websocket\Estimation;
use SergiX44\Gradio\DTO\Websocket\Message;
use SergiX44\Gradio\DTO\Websocket\ProcessCompleted;
use SergiX44\Gradio\DTO\Websocket\ProcessGenerating;
use SergiX44\Gradio\DTO\Websocket\ProcessStarts;
use SergiX44\Gradio\DTO\Websocket\QueueFull;
use SergiX44\Gradio\DTO\Websocket\SendData;
use SergiX44\Gradio\DTO\Websocket\SendHash;
use SergiX44\Gradio\Event\Event;
use SergiX44\Gradio\Exception\GradioException;
use SergiX44\Gradio\Exception\QueueFullException;

class Client extends RemoteClient
{
    private const HTTP_PREDICT = 'run/predict';

    private const WS_PREDICT = 'queue/join';

    private const HTTP_CONFIG = 'config';

    protected Config $config;

    private string $sessionHash;

    private array $endpoints = [];

    private ?string $hfToken;

    public function __construct(string $src, string $hfToken = null, Config $config = null)
    {
        parent::__construct($src);
        $this->config = $config ?? $this->get(self::HTTP_CONFIG, dto: Config::class);
        $this->loadEndpoints($this->config->dependencies);
        $this->sessionHash = substr(md5(microtime()), 0, 11);
        $this->hfToken = $hfToken;
    }

    protected function loadEndpoints(array $dependencies): void
    {
        foreach ($dependencies as $index => $dep) {
            $endpoint = new Endpoint(
                $this,
                $index,
                !empty($dep['api_name']) ? $dep['api_name'] : null,
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

    public function predict(array $arguments, string $apiName = null, int $fnIndex = null): ?Output
    {
        if ($apiName === null && $fnIndex === null) {
            throw new InvalidArgumentException('You must provide an apiName or fnIndex');
        }

        $apiName = $apiName !== null ? str_replace('/', '', $apiName) : null;
        $endpoint = $this->endpoints[$apiName ?? $fnIndex] ?? null;

        if ($endpoint === null) {
            throw new InvalidArgumentException('Endpoint not found');
        }

        return $this->submit($endpoint, $arguments);
    }

    private function submit(Endpoint $endpoint, array $arguments): ?Output
    {
        $payload = $this->preparePayload($arguments);
        $this->fireEvent(Event::SUBMIT, $payload);

        if ($endpoint->useWebsockets) {
            return $this->websocketLoop($endpoint, $payload);
        }

        return $this->post(self::HTTP_PREDICT, ['data' => $payload], Output::class);
    }

    private function preparePayload(array $arguments): array
    {
        return array_map(static function ($arg) {
            if (is_resource($arg)) {
                $filename = stream_get_meta_data($arg)['uri'];
                $contents = stream_get_contents($filename);
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($contents);

                return [
                    'data' => "data:$mime;base64,".base64_encode($contents),
                    'name' => basename($filename),
                ];
            }

            if (is_string($arg) && file_exists($arg)) {
                $contents = file_get_contents($arg);
                $mime = mime_content_type($arg);

                return [
                    'data' => "data:$mime;base64,".base64_encode($contents),
                    'name' => basename($arg),
                ];
            }

            return $arg;
        }, $arguments);
    }

    /**
     * @throws GradioException
     * @throws QueueFullException
     * @throws \JsonException
     */
    private function websocketLoop(Endpoint $endpoint, array $payload): ?Output
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
                $this->fireEvent(Event::PROCESS_COMPLETED, [$message]);
                if ($message->success) {
                    $this->fireEvent(Event::PROCESS_SUCCESS, [$message]);
                } else {
                    $this->fireEvent(Event::PROCESS_FAILED, [$message]);
                }
                break;
            } elseif ($message instanceof ProcessStarts) {
                $this->fireEvent(Event::PROCESS_STARTS, [$message]);
            } elseif ($message instanceof ProcessGenerating) {
                $this->fireEvent(Event::PROCESS_GENERATING, [$message]);
            } else {
                throw new GradioException("'Unknown message type $data");
            }
        }

        $ws->close();

        return $message?->output;
    }
}
