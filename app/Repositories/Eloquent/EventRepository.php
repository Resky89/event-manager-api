<?php

namespace App\Repositories\Eloquent;

use App\Helpers\ApiQuery;
use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        $query = ApiQuery::for(Event::query())
            ->searchable(['title', 'description', 'location'])
            ->sortable(['id', 'title', 'start_time', 'end_time', 'created_at'])
            ->filterable([
                'id' => 'int',
                'organizer_id' => 'int',
                'title' => 'string_like',
                'location' => 'string_like',
                'start_time' => 'datetime',
                'end_time' => 'datetime',
                'created_at' => 'datetime',
            ])
            ->apply($params);

        return $query->paginate($perPage)->appends($params);
    }

    public function find(int $id): ?Event
    {
        return Event::find($id);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event;
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }
}
