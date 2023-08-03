<?php

namespace SergiX44\Gradio\Exception;

use Throwable;

class QueueFullException extends GradioException
{
    public function __construct(string $message = 'Queue full.', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
