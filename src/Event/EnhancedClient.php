<?php

namespace SergiX44\Gradio\Event;

use WebSocket\Client;

class EnhancedClient extends Client
{
    public function __construct(string $url, array $options = [])
    {
        self::$default_options['timeout'] = 30;
        parent::__construct($url, $options);
    }

    public function sendJson(array $data): void
    {
        $this->text(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
