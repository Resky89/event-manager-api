<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return \App\Helpers\ResponseFormatter::error(null, 401);
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                return \App\Helpers\ResponseFormatter::error(null, 401);
            }
            // Set the authenticated user on the 'api' guard so auth('api')->user() works downstream
            Auth::shouldUse('api');
            Auth::guard('api')->setUser($user);
        } catch (\Throwable $e) {
            return \App\Helpers\ResponseFormatter::error(null, 401, ['exception' => $e->getMessage()]);
        }

        return $next($request);
    }
}

