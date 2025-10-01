<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@example.com')->first()
            ?? User::where('role', 'user')->first();

        if (!$user) {
            $this->command?->warn('No regular user found. Skipping RegistrationSeeder.');
            return;
        }

        $events = Event::all();
        if ($events->isEmpty()) {
            $this->command?->warn('No events found. Skipping RegistrationSeeder.');
            return;
        }

        foreach ($events as $event) {
            $ticket = Ticket::where('event_id', $event->id)->inRandomOrder()->first();
            if (!$ticket) {
                $this->command?->warn("No tickets for event {$event->id}. Skipping registration.");
                continue;
            }

            $data = [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'ticket_id' => $ticket->id,
                'registered_at' => Carbon::now()->subDays(rand(1, 10)),
                'status' => 'confirmed',
            ];

            Registration::updateOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'event_id' => $data['event_id'],
                    'ticket_id' => $data['ticket_id'],
                ],
                $data
            );
        }
    }
}
