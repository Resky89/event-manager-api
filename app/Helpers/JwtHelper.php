<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JwtHelper
{
    public static function issue(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    public static function tokenFrom(Request $request): ?string
    {
        return $request->bearerToken();
    }

    public static function user(): ?User
    {
        try {
            return auth('api')->user();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function claims(?string $token = null): array
    {
        try {
            $payload = $token ? JWTAuth::setToken($token)->getPayload() : JWTAuth::parseToken()->getPayload();
            return $payload->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
