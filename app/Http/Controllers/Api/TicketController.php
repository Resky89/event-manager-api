<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiQuery;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\TicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private readonly TicketService $tickets)
    {
    }

    public function indexByEvent(int $eventId, Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
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
            ->apply($request->query());

        $paginator = $query->paginate($perPage)->appends($request->query());
        $data = [
            'items' => TicketResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
        return ResponseFormatter::success($data);
    }

    public function store(TicketRequest $request)
    {
        $ticket = $this->tickets->create($request->validated());
        return ResponseFormatter::success(new TicketResource($ticket), 'Created', 201);
    }

    public function update(TicketRequest $request, int $id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return ResponseFormatter::error(null, 404);
        }
        $ticket = $this->tickets->update($ticket, $request->validated());
        return ResponseFormatter::success(new TicketResource($ticket), 'Updated');
    }

    public function destroy(int $id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return ResponseFormatter::error(null, 404);
        }
        $ticket->delete();
        return ResponseFormatter::success(null, 'Deleted');
    }
}

