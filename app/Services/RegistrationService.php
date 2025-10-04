<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function create(array $data): Registration
    {
        return DB::transaction(function () use ($data) {
            $registration = Registration::create($data);

            // Create pending payment based on ticket price
            $ticket = Ticket::findOrFail($registration->ticket_id);
            Payment::create([
                'registration_id' => $registration->id,
                'amount' => $ticket->price,
                'status' => 'pending',
            ]);

            return $registration->load('payment');
        });
    }
}
