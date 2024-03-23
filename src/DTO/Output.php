<?php

namespace SergiX44\Gradio\DTO;

class Output
{
    public bool $is_generating = false;

    public float $duration = 0.0;

    public float $average_duration = 0.0;

    public array $data = [];

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

    public function getOutputs(): array
    {
        return $this->data ?? [];
    }

    public function getOutput(int $index = 0): mixed
    {
        return $this->data[$index] ?? null;
    }
}
