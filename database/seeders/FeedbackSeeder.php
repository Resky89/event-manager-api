<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@example.com')->first()
            ?? User::where('role', 'user')->first();

        if (!$user) {
            $this->command?->warn('No regular user found. Skipping FeedbackSeeder.');
            return;
        }

        $events = Event::all();
        if ($events->isEmpty()) {
            $this->command?->warn('No events found. Skipping FeedbackSeeder.');
            return;
        }

        $comments = [
            'Acara bagus dan informatif.',
            'Pembicara sangat berpengalaman.',
            'Lokasi strategis dan nyaman.',
            'Akan ikut lagi kalau ada event serupa.',
        ];

        foreach ($events as $event) {
            $rating = rand(3, 5);
            $comment = $comments[array_rand($comments)];

            Feedback::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                ],
                [
                    'rating' => $rating,
                    'comment' => $comment,
                ]
            );
        }
    }
}
