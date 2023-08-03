<?php

namespace SergiX44\Gradio\DTO;

class Result
{
    public bool $is_generating = false;

    public float $duration = 0.0;

    public float $average_duration = 0.0;

    public array $data = [];

    public function getOutputs(): array
    {
        return $this->data ?? [];
    }

    public function getOutput(int $index = 0): mixed
    {
        return $this->data[$index] ?? null;
    }
}
