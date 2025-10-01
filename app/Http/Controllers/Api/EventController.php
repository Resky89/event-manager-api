<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiQuery;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(private readonly EventService $events)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
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
            ->apply($request->query());

        $paginator = $query->paginate($perPage)->appends($request->query());
        $data = [
            'items' => EventResource::collection($paginator->items()),
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
        $event = Event::find($id);
        if (!$event) {
            return ResponseFormatter::error(null, 404);
        }
        return ResponseFormatter::success(new EventResource($event));
    }

    public function store(EventRequest $request)
    {
        $data = $request->validated();
        // If organizer_id omitted, use current user
        $data['organizer_id'] = $data['organizer_id'] ?? (auth('api')->id());
        $event = $this->events->create($data);
        return ResponseFormatter::success(new EventResource($event), 'Created', 201);
    }

    public function update(EventRequest $request, int $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return ResponseFormatter::error(null, 404);
        }
        $event = $this->events->update($event, $request->validated());
        return ResponseFormatter::success(new EventResource($event), 'Updated');
    }

    public function destroy(int $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return ResponseFormatter::error(null, 404);
        }
        $event->delete();
        return ResponseFormatter::success(null, 'Deleted');
    }
}

