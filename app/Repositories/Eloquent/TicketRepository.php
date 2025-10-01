<?php

namespace App\Repositories\Eloquent;

use App\Helpers\ApiQuery;
use App\Models\Ticket;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketRepository implements TicketRepositoryInterface
{
    public function paginateByEvent(int $eventId, int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        $base = Ticket::query()->where('event_id', $eventId);
        $query = ApiQuery::for($base)
            ->searchable(['type'])
            ->sortable(['id', 'type', 'price', 'quantity', 'created_at'])
            ->filterable([
                'id' => 'int',
                'type' => 'string_like',
                'price' => 'numeric',
                'quantity' => 'int',
                'created_at' => 'datetime',
            ])
            ->apply($params);

        return $query->paginate($perPage)->appends($params);
    }

    public function find(int $id): ?Ticket
    {
        return Ticket::find($id);
    }

    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        $ticket->update($data);
        return $ticket;
    }

    public function delete(Ticket $ticket): void
    {
        $ticket->delete();
    }
}
