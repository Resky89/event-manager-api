<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::all();
        if ($events->isEmpty()) {
            $this->command?->warn('No events found. Skipping TicketSeeder.');
            return;
        }

        foreach ($events as $event) {
            $tickets = [
                [
                    'type' => 'General',
                    'price' => 100000,
                    'quantity' => 100,
                ],
                [
                    'type' => 'VIP',
                    'price' => 250000,
                    'quantity' => 20,
                ],
                [
                    'type' => 'Early Bird',
                    'price' => 75000,
                    'quantity' => 30,
                ],
            ];

            foreach ($tickets as $data) {
                Ticket::updateOrCreate(
                    ['event_id' => $event->id, 'type' => $data['type']],
                    array_merge($data, ['event_id' => $event->id])
                );
            }
        }
    }
}
