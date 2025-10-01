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

            [$accessToken, $refreshToken, $refreshTtlSeconds] = $this->issueTokens($user, $request);

            return [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => $refreshTtlSeconds,
            ];
        });
    }

    public function login(string $email, string $password, Request $request): array
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            throw new HttpException(401, 'Invalid credentials');
        }

        [$accessToken, $refreshToken, $refreshTtlSeconds] = $this->issueTokens($user, $request);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $refreshTtlSeconds,
        ];
    }

    public function refresh(string $refreshToken, Request $request): array
    {
        // Validate refresh JWT
        try {
            $payload = JWTAuth::setToken($refreshToken)->getPayload();
        } catch (\Throwable $e) {
            throw new HttpException(401, 'Invalid refresh token');
        }

        if (($payload['token_type'] ?? null) !== 'refresh') {
            throw new HttpException(401, 'Invalid token type');
        }

        $userId = (int) ($payload['sub'] ?? 0);
        if (!$userId) {
            throw new HttpException(401, 'Invalid refresh token');
        }

        // Check DB session to allow revocation and single-session control
        $session = UserSession::query()
            ->where('user_id', $userId)
            ->where('refresh_token', $refreshToken)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            throw new HttpException(401, 'Invalid refresh token');
        }

        $user = User::findOrFail($userId);

        // Issue new tokens and rotate refresh session
        [$accessToken, $newRefreshToken, $refreshTtlSeconds] = $this->issueTokens($user, $request);

        $session->update([
            'refresh_token' => $newRefreshToken,
            'expires_at' => now()->addSeconds($refreshTtlSeconds),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => $refreshTtlSeconds,
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

    /**
     * Issue access and refresh JWT with user claims and return [access, refresh, refreshTtlSeconds].
     */
    private function issueTokens(User $user, Request $request): array
    {
        $claims = [
            'token_type' => 'access',
            'uid' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
        ];

        // Access token with default TTL
        $accessToken = JWTAuth::claims($claims)->fromUser($user);

        // Refresh token with configured refresh TTL and token_type=refresh
        $factory = JWTAuth::factory();
        $originalTtl = $factory->getTTL();
        $factory->setTTL((int) config('jwt.refresh_ttl'));
        $refreshClaims = $claims;
        $refreshClaims['token_type'] = 'refresh';
        $refreshToken = JWTAuth::claims($refreshClaims)->fromUser($user);
        $factory->setTTL($originalTtl); // restore

        $refreshTtlSeconds = (int) (config('jwt.refresh_ttl') * 60);

        // Persist/rotate session with refresh JWT (to allow revocation)
        UserSession::updateOrCreate(
            ['user_id' => $user->id],
            [
                'refresh_token' => $refreshToken,
                'expires_at' => now()->addSeconds($refreshTtlSeconds),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'revoked_at' => null,
            ]
        );

        return [$accessToken, $refreshToken, $refreshTtlSeconds];
    }
}

