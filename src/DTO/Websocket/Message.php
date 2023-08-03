<?php

namespace SergiX44\Gradio\DTO\Websocket;

use SergiX44\Gradio\Websocket\MessageResolver;
use SergiX44\Gradio\Websocket\MessageType;

#[MessageResolver]
abstract class Message
{
    public MessageType $msg;
}
