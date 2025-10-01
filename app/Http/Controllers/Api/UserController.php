<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $paginator = $this->users->paginate($perPage, $request->query());
        $data = [
            'items' => UserResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
        return ResponseFormatter::success($data);
    }

    public function show(int $id)
    {
        $user = $this->users->find($id);
        if (!$user) {
            return ResponseFormatter::error(null, 404);
        }
        return ResponseFormatter::success(new UserResource($user));
    }

    public function store(UserRequest $request)
    {
        $user = $this->users->create($request->validated());
        return ResponseFormatter::success(new UserResource($user), 'Created', 201);
    }

    public function update(UserRequest $request, int $id)
    {
        $user = $this->users->find($id);
        if (!$user) {
            return ResponseFormatter::error(null, 404);
        }
        $user = $this->users->update($user, $request->validated());
        return ResponseFormatter::success(new UserResource($user), 'Updated');
    }

    public function destroy(int $id)
    {
        $user = $this->users->find($id);
        if (!$user) {
            return ResponseFormatter::error(null, 404);
        }
        $this->users->delete($user);
        return ResponseFormatter::success(null, 'Deleted');
    }
}

