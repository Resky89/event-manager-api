<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    public function create(array $data): User
    {
        return $this->users->create($data);
    }

    public function update(User $user, array $data): User
    {
        return $this->users->update($user, $data);
    }
}
