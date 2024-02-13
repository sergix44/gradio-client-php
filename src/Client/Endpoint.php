<?php

namespace SergiX44\Gradio\Client;

use SergiX44\Gradio\DTO\Config;

readonly class Endpoint
{
    public function __construct(
        private Config $config,
        public int $index,
        private readonly array $data
    ) {
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function skipsQueue(): bool
    {
        return ! ($this->data['queue'] ?? $this->config->enable_queue);
    }

    public function apiName(): ?string
    {
        return ! empty($this->data['api_name']) ? $this->data['api_name'] : null;
    }

    public function uri()
    {
        $name = $this->apiName();
        if ($name !== null) {
            $name = str_replace('/', '', $name);

            return "run/$name";
        }

        return 'run/predict';
    }
}
