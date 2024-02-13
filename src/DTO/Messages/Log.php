<?php

namespace SergiX44\Gradio\DTO\Messages;

class Log extends Message
{
    public ?string $log = null;

    public ?string $level = null;

    public ?string $event_id = null;
}
