<?php

namespace SergiX44\Gradio\DTO\Messages;

class Estimation extends Message
{
    public ?int $rank = null;

    public ?int $queue_size = null;

    public ?float $avg_event_process_time = null;

    public ?float $avg_event_concurrent_process_time = null;

    public ?float $rank_eta = null;

    public ?float $queue_eta = null;
}
