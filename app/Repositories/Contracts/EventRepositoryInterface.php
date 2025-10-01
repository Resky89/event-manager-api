<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;
    public function find(int $id): ?Event;
    public function create(array $data): Event;
    public function update(Event $event, array $data): Event;
    public function delete(Event $event): void;
}
