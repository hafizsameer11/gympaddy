<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // User login
    public function login(LoginRequest $request)
    {
        return $this->authService->login($request->validated());
    }

    // User registration
    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request->validated());
    }

    // Forgot password (send reset link)
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        return $this->authService->forgotPassword($request->validated());
    }

    // Verify OTP (as password reset token verification)
    public function verifyOtp(VerifyOtpRequest $request)
    {
        return $this->authService->verifyOtp($request->validated());
    }
}
