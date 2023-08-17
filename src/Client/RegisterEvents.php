<?php

namespace SergiX44\Gradio\Client;

use SergiX44\Gradio\Event\Event;
use SergiX44\Gradio\Event\EventHandler;

abstract class RegisterEvents
{
    private array $events = [];

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

    public function onProcessGenerating(callable $callback): EventHandler
    {
        return $this->events[Event::PROCESS_GENERATING->value] = new EventHandler(Event::PROCESS_GENERATING, $callback);
    }

    public function onProcessStarts(callable $callback): EventHandler
    {
        return $this->events[Event::PROCESS_STARTS->value] = new EventHandler(Event::PROCESS_STARTS, $callback);
    }

    public function onProcessCompleted(callable $callback): EventHandler
    {
        return $this->events[Event::PROCESS_COMPLETED->value] = new EventHandler(Event::PROCESS_COMPLETED, $callback);
    }

    public function onProcessSuccess(callable $callback): EventHandler
    {
        return $this->events[Event::PROCESS_SUCCESS->value] = new EventHandler(Event::PROCESS_SUCCESS, $callback);
    }

    public function onProcessFailed(callable $callback): EventHandler
    {
        return $this->events[Event::PROCESS_FAILED->value] = new EventHandler(Event::PROCESS_FAILED, $callback);
    }

    protected function fireEvent(Event $event, array $data = []): void
    {
        if (isset($this->events[$event->value])) {
            $events = is_array($this->events[$event->value]) ? $this->events[$event->value] : [$this->events[$event->value]];
            foreach ($events as $e) {
                $e(...$data);
            }
        }
    }
}
