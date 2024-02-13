<?php

namespace SergiX44\Gradio\DTO\Messages;

use SergiX44\Gradio\DTO\Output;

class ProcessCompleted extends Message
{
    public bool $success = false;

    public ?Output $output = null;
}
