<?php

namespace Telegram\Bot\Events;

use League\Event\EmitterInterface;
use League\Event\EventInterface;

/**
 * EmitsEvents.
 */
trait EmitsEvents
{
    /** @var EmitterInterface */
    protected $eventEmitter;

    /**
     * Emit an event.
     *
     * @param  EventInterface|string  $event
     * @return bool true if emitted, false otherwise.
     *
     * @throws \InvalidArgumentException
     */
    protected function emitEvent($event): bool
    {
        if (is_null($this->eventEmitter)) {
            return false;
        }

        $this->validateEvent($event);

        $this->eventEmitter->emit($event);

        return true;
    }

    /**
     * Emit events in batch.
     *
     * @param  EventInterface[]|string[]  $events
     * @return bool true if all emitted, false otherwise
     *
     * @throws \InvalidArgumentException
     */
    private function emitBatchOfEvents(array $events): bool
    {
        if (is_null($this->eventEmitter)) {
            return false;
        }

        foreach ($events as $e) {
            $this->validateEvent($e);
        }

        $this->emitBatchOfEvents($events);

        return true;
    }

    /**
     * Returns an event emitter.
     */
    public function getEventEmitter(): EmitterInterface
    {
        return $this->eventEmitter;
    }

    /**
     * Set an event emitter.
     *
     * @param  EmitterInterface  $eventEmitter
     * @return $this
     */
    public function setEventEmitter($eventEmitter)
    {
        $this->eventEmitter = $eventEmitter;

        return $this;
    }

    /**
     * @return void
     */
    private function validateEvent($event)
    {
        if (! is_string($event) && ! $event instanceof EventInterface) {
            throw new \InvalidArgumentException('Event must be either be of type "string" or instance of League\Event\EventInterface');
        }
    }
}
