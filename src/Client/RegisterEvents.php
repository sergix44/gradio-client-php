<?php

namespace SergiX44\Gradio\Client;

use SergiX44\Gradio\Event\Event;
use SergiX44\Gradio\Event\EventHandler;

abstract class RegisterEvents
{
    public array $events = [];

    public function onSubmit(callable $callback): EventHandler
    {
        return $this->events[Event::SUBMIT->value] = new EventHandler(Event::SUBMIT, $callback);
    }

    public function onQueueEstimation(callable $callback): EventHandler
    {
        return $this->events[Event::QUEUE_ESTIMATION->value] = new EventHandler(Event::QUEUE_ESTIMATION, $callback);
    }

    public function onQueueFull(callable $callback): EventHandler
    {
        return $this->events[Event::QUEUE_FULL->value] = new EventHandler(Event::QUEUE_FULL, $callback);
    }

    protected function fireEvent(Event $event, array $data = []): void
    {
        if (isset($this->events[$event->value])) {
            $events = is_array($this->events[$event->value]) ? $this->events[$event->value] : [$this->events[$event->value]];
            foreach ($events as $e) {
                $e($data);
            }
        }
    }
}
