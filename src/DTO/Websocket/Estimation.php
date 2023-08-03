<?php

namespace SergiX44\Gradio\DTO\Websocket;

class Estimation extends Message
{

    public int $rank;

    public int $queue_size;

    public float $avg_event_process_time;

    public float $avg_event_concurrent_process_time;

    public float $rank_eta;
    public float $queue_eta;


}
