<?php

namespace SergiX44\Gradio\DTO\Websocket;

use SergiX44\Gradio\DTO\Result;

class ProcessCompleted extends Message
{
    public bool $success = false;

    public ?Result $output = null;
}
