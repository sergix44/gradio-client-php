<?php

namespace SergiX44\Gradio\Websocket;

use InvalidArgumentException;
use SergiX44\Gradio\DTO\Websocket\Estimation;
use SergiX44\Gradio\DTO\Websocket\ProcessCompleted;
use SergiX44\Gradio\DTO\Websocket\ProcessStarts;
use SergiX44\Gradio\DTO\Websocket\QueueFull;
use SergiX44\Gradio\DTO\Websocket\SendData;
use SergiX44\Gradio\DTO\Websocket\SendHash;
use SergiX44\Hydrator\Annotation\ConcreteResolver;

#[\Attribute] class MessageResolver extends ConcreteResolver
{
    public function concreteFor(array $data): ?string
    {
        $msg = $data['msg'] ?? throw new InvalidArgumentException('Missing msg key');

        return match ($msg) {
            MessageType::SEND_HASH->value => SendHash::class,
            MessageType::SEND_DATA->value => SendData::class,
            MessageType::QUEUE_FULL->value => QueueFull::class,
            MessageType::QUEUE_ESTIMATION->value => Estimation::class,
            MessageType::PROCESS_STARTS->value => ProcessStarts::class,
            MessageType::PROCESS_COMPLETED->value => ProcessCompleted::class,
            default => throw new InvalidArgumentException('Unknown msg type'),
        };
    }
}
