<?php

namespace SergiX44\Gradio\Event;

enum Event: string
{
    case SUBMIT = 'EVENT_SUBMITTED';

    case QUEUE_FULL = 'EVENT_QUEUE_FULL';
    case QUEUE_ESTIMATION = 'EVENT_QUEUE_ESTIMATION';

}
