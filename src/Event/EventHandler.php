<?php

namespace SergiX44\Gradio\Event;

class EventHandler
{

    public function __construct(private Event $event, private $callback)
    {
    }

    public function __invoke()
    {
        return call_user_func_array($this->callback, func_get_args());
    }

}
