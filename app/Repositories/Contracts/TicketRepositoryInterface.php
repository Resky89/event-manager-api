<?php

namespace App\Repositories\Contracts;

use App\Models\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TicketRepositoryInterface
{
    public function paginateByEvent(int $eventId, int $perPage = 15, array $params = []): LengthAwarePaginator;
    public function find(int $id): ?Ticket;
    public function create(array $data): Ticket;
    public function update(Ticket $ticket, array $data): Ticket;
    public function delete(Ticket $ticket): void;
}
