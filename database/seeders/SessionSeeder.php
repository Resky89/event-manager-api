<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SessionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@example.com')->first() ?? User::first();
        if (!$user) {
            $this->command?->warn('No user found. Skipping SessionSeeder.');
            return;
        }

        UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'revoked_at' => null,
            ],
            [
                'refresh_token' => Str::random(64),
                'expires_at' => Carbon::now()->addDays(7),
                'user_agent' => 'Seeder/1.0',
                'ip_address' => '127.0.0.1',
            ]
        );
    }
}
