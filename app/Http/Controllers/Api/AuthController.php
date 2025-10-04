<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
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
        $refresh = $request->header('X-Refresh-Token');
        if (!$refresh) {
            return ResponseFormatter::error('X-Refresh-Token header is required', 422);
        }
        $payload = $this->auth->refresh((string) $refresh, $request);
        return ResponseFormatter::success($payload, 'Refreshed');
    }

    public function logout(Request $request)
    {
        $refresh = $request->header('X-Refresh-Token');
        if (!$refresh) {
            return ResponseFormatter::error('X-Refresh-Token header is required', 422);
        }
        $this->auth->logout((string) $refresh);
        return ResponseFormatter::success(null, 'Logged out');
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $data = $request->validated();
        $payload = $this->auth->verifyOtp($data['email'], $data['otp_code'], $request);
        return ResponseFormatter::success($payload, 'Verified');
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        $data = $request->validated();
        $this->auth->resendOtp($data['email']);
        return ResponseFormatter::success(null, 'OTP resent');
    }
}

