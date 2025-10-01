<?php

namespace App\Services;

use App\Models\Event;

class EventService
{
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event;
    }
}
