<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return \App\Helpers\ResponseFormatter::error(null, 401);
        }

        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            return \App\Helpers\ResponseFormatter::error(null, 403);
        }

        return $next($request);
    }
}
