<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@example.com')->first()
            ?? User::where('role', 'organizer')->first();

        if (!$organizer) {
            $this->command?->warn('No organizer found. Skipping EventSeeder.');
            return;
        }

        $now = Carbon::now();
        $events = [
            [
                'title' => 'Tech Meetup',
                'description' => 'Monthly tech meetup.',
                'location' => 'Jakarta',
                'start_time' => $now->copy()->addDays(3)->setTime(18, 0, 0),
                'end_time' => $now->copy()->addDays(3)->setTime(21, 0, 0),
            ],
            [
                'title' => 'Product Launch',
                'description' => 'Launching our new product.',
                'location' => 'Bandung',
                'start_time' => $now->copy()->addDays(10)->setTime(10, 0, 0),
                'end_time' => $now->copy()->addDays(10)->setTime(12, 0, 0),
            ],
            [
                'title' => 'Workshop Laravel',
                'description' => 'Hands-on Laravel workshop.',
                'location' => 'Surabaya',
                'start_time' => $now->copy()->addDays(20)->setTime(9, 0, 0),
                'end_time' => $now->copy()->addDays(20)->setTime(16, 0, 0),
            ],
        ];

        foreach ($events as $data) {
            Event::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'organizer_id' => $organizer->id,
                ])
            );
        }
    }
}
