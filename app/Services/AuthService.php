<?php

namespace App\Services;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthService
{
    public function register(array $data, Request $request): array
    {
        return DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // hashed by cast
                'role' => $data['role'] ?? 'user',
                'is_active' => $data['is_active'] ?? true,
            ]);

            $accessToken = JWTAuth::fromUser($user);
            $refresh = $this->createRefreshSession($user->id, $request);

            return [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refresh['token'],
                'expires_in' => $refresh['ttl'],
            ];
        });
    }

    public function login(string $email, string $password, Request $request): array
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            throw new HttpException(401, 'Invalid credentials');
        }

        $accessToken = JWTAuth::fromUser($user);
        $refresh = $this->createRefreshSession($user->id, $request);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refresh['token'],
            'expires_in' => $refresh['ttl'],
        ];
    }

    public function refresh(string $refreshToken, Request $request): array
    {
        $session = UserSession::query()
            ->where('refresh_token', $refreshToken)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            throw new HttpException(401, 'Invalid refresh token');
        }

        $user = User::findOrFail($session->user_id);
        $accessToken = JWTAuth::fromUser($user);

        // rotate refresh token
        $session->update([
            'refresh_token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $session->refresh_token,
            'expires_in' => 7 * 24 * 60 * 60,
        ];
    }

    public function logout(?string $refreshToken): void
    {
        if ($refreshToken) {
            UserSession::where('refresh_token', $refreshToken)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Throwable $e) {
            // ignore if token invalid/missing
        }
    }

    private function createRefreshSession(int $userId, Request $request): array
    {
        $token = Str::random(64);
        $ttl = 7 * 24 * 60 * 60; // 7 days in seconds

        UserSession::create([
            'user_id' => $userId,
            'refresh_token' => $token,
            'expires_at' => now()->addSeconds($ttl),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return ['token' => $token, 'ttl' => $ttl];
    }
}
