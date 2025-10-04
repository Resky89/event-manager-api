<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiQuery;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\PaymentPayRequest;
use App\Http\Resources\RegistrationResource;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\Payment;
use App\Services\RegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $registrations)
    {
    }

    public function store(RegistrationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth('api')->id();

        // ensure ticket belongs to event
        $belongs = Ticket::query()->where('id', $data['ticket_id'])->where('event_id', $data['event_id'])->exists();
        if (!$belongs) {
            return ResponseFormatter::error(null, 422, ['detail' => 'Ticket does not belong to the event']);
        }

        $registration = $this->registrations->create($data);
        return ResponseFormatter::success(new RegistrationResource($registration), 'Registered', 201);
    }

    public function myRegistrations(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $base = Registration::query()->with('payment')->where('user_id', auth('api')->id());
        $query = ApiQuery::for($base)
            ->searchable(['status'])
            ->sortable(['id', 'event_id', 'ticket_id', 'status', 'registered_at', 'created_at'])
            ->filterable([
                'id' => 'int',
                'event_id' => 'int',
                'ticket_id' => 'int',
                'status' => 'string',
                'registered_at' => 'datetime',
                'created_at' => 'datetime',
            ])
            ->apply($request->query());

        $paginator = $query->paginate($perPage)->appends($request->query());
        $data = [
            'items' => RegistrationResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
        return ResponseFormatter::success($data);
    }

    public function pay(PaymentPayRequest $request, int $id)
    {
        $registration = Registration::with('payment')->find($id);
        if (!$registration) {
            return ResponseFormatter::error(null, 404);
        }
        if ($registration->user_id !== auth('api')->id()) {
            return ResponseFormatter::error(null, 403);
        }
        $payment = $registration->payment;
        if (!$payment || $payment->status === 'paid') {
            return ResponseFormatter::error('Payment not pending', 422);
        }

        $data = $request->validated();
        $payment->method = $data['method'] ?? $payment->method;
        $payment->transaction_ref = $data['transaction_ref'];
        $payment->status = 'paid';
        $payment->paid_at = now();
        $payment->save();

        $registration->status = 'confirmed';
        $registration->save();

        return ResponseFormatter::success(new RegistrationResource($registration->fresh('payment')), 'Paid');
    }
}

