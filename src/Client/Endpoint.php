<?php

namespace SergiX44\Gradio\Client;

use SergiX44\Gradio\Client;

readonly class Endpoint
{
    public function __construct(
        public Client $client,
        public int $index,
        public ?string $apiName,
        public bool $useWebsockets,
        public int $argsCount = 1,
    ) {
    }
}
