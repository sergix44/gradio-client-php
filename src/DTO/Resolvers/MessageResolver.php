<?php

namespace SergiX44\Gradio\DTO\Resolvers;

use InvalidArgumentException;
use SergiX44\Gradio\DTO\Messages\Estimation;
use SergiX44\Gradio\DTO\Messages\Log;
use SergiX44\Gradio\DTO\Messages\Message;
use SergiX44\Gradio\DTO\Messages\ProcessCompleted;
use SergiX44\Gradio\DTO\Messages\ProcessGenerating;
use SergiX44\Gradio\DTO\Messages\ProcessStarts;
use SergiX44\Gradio\DTO\Messages\QueueFull;
use SergiX44\Gradio\DTO\Messages\SendData;
use SergiX44\Gradio\DTO\Messages\SendHash;
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
            MessageType::PROCESS_GENERATING->value => ProcessGenerating::class,
            MessageType::PROCESS_COMPLETED->value => ProcessCompleted::class,
            MessageType::LOG->value => Log::class,
            default => (new class extends Message
            {
            })::class,
        };
    }
}
