<?php

namespace App\Services;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use App\Models\UserSession;
use App\Notifications\VerifyOtpNotification;
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
                'password' => $data['password'],
                'role' => 'user',
                'is_active' => true,
            ]);

            // If role is user, require OTP verification before login
            if (($user->role ?? 'user') === 'user') {
                $this->generateOtp($user);
                return [
                    'user' => $user,
                    'requires_verification' => true,
                ];
            }

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

        if ($user->role === 'user' && !$user->email_verified_at) {
            throw new HttpException(403, 'Please verify your account via OTP');
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

    private function generateOtp(User $user): void
    {
        $code = (string) random_int(100000, 999999);
        $user->otp_code = $code;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();
        // Send OTP via email (configure MAIL_* in .env)
        try {
            $user->notify(new VerifyOtpNotification($code));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function verifyOtp(string $email, string $otp, Request $request): array
    {
        $user = User::where('email', $email)->first();
        if (!$user || $user->role !== 'user') {
            throw new HttpException(404, 'User not found');
        }
        if (!$user->otp_code || !$user->otp_expires_at || $user->otp_code !== $otp || now()->greaterThan($user->otp_expires_at)) {
            throw new HttpException(422, 'Invalid or expired OTP');
        }
        $user->email_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        [$accessToken, $refreshToken, $refreshTtlSeconds] = $this->issueTokens($user, $request);
        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $refreshTtlSeconds,
        ];
    }

    public function resendOtp(string $email): void
    {
        $user = User::where('email', $email)->first();
        if (!$user || $user->role !== 'user') {
            throw new HttpException(404, 'User not found');
        }
        $this->generateOtp($user);
    }
}

