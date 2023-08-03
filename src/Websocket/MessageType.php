<?php

namespace SergiX44\Gradio\Websocket;

enum MessageType: string
{
    case SEND_HASH = 'send_hash';
    case SEND_DATA = 'send_data';
    case QUEUE_FULL = 'queue_full';
    case QUEUE_ESTIMATION = 'estimation';
    case PROCESS_STARTS = 'process_starts';
    case PROCESS_COMPLETED = 'process_completed';
}
