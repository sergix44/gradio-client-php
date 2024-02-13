<?php

namespace SergiX44\Gradio\DTO\Messages;

use SergiX44\Gradio\DTO\Resolvers\MessageResolver;
use SergiX44\Gradio\DTO\Resolvers\MessageType;
use SergiX44\Hydrator\Resolver\EnumOrScalar;

#[MessageResolver]
abstract class Message
{
    #[EnumOrScalar]
    public MessageType|string $msg;

    private array $_extra = [];

    public function __set(string $name, $value): void
    {
        $this->_extra[$name] = $value;
    }

    public function __get(string $name)
    {
        return $this->_extra[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->_extra[$name]);
    }
}
