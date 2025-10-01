<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function register(RegisterRequest $request)
    {
        $payload = $this->auth->register($request->validated(), $request);
        return ResponseFormatter::success($payload, 'Registered', 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $payload = $this->auth->login($data['email'], $data['password'], $request);
        return ResponseFormatter::success($payload, 'Logged in');
    }

    public function refresh(Request $request)
    {
        $refresh = $request->input('refresh_token') ?? $request->header('X-Refresh-Token');
        $payload = $this->auth->refresh((string) $refresh, $request);
        return ResponseFormatter::success($payload, 'Refreshed');
    }

    public function logout(Request $request)
    {
        $refresh = $request->input('refresh_token') ?? $request->header('X-Refresh-Token');
        $this->auth->logout($refresh ? (string) $refresh : null);
        return ResponseFormatter::success(null, 'Logged out');
    }
}
