<?php

namespace App\Repositories\Eloquent;

use App\Helpers\ApiQuery;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        $query = ApiQuery::for(User::query())
            ->searchable(['name', 'email'])
            ->sortable(['id', 'name', 'email', 'role', 'is_active', 'created_at'])
            ->filterable([
                'id' => 'int',
                'name' => 'string_like',
                'email' => 'string_like',
                'role' => 'string',
                'is_active' => 'bool',
                'created_at' => 'datetime',
            ])
            ->apply($params);

        return $query->paginate($perPage)->appends($params);
    }

    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
