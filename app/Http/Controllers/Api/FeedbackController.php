<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiQuery;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\FeedbackRequest;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(FeedbackRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth('api')->id();

        $feedback = Feedback::create($data);
        return ResponseFormatter::success(new FeedbackResource($feedback), 'Created', 201);
    }

    public function listByEvent(int $eventId, Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $base = Feedback::query()->where('event_id', $eventId);
        $query = ApiQuery::for($base)
            ->searchable(['comment'])
            ->sortable(['id', 'rating', 'created_at'])
            ->filterable([
                'id' => 'int',
                'user_id' => 'int',
                'rating' => 'int',
                'comment' => 'string_like',
                'created_at' => 'datetime',
            ])
            ->apply($request->query());

        $paginator = $query->paginate($perPage)->appends($request->query());
        $data = [
            'items' => FeedbackResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
        return ResponseFormatter::success($data);
    }
}

