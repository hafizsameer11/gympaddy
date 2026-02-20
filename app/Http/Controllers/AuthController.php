<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\ResetPasswordRequest;

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
    public function adminLogin(LoginRequest $request)
    {
        return $this->authService->adminLogin($request->validated());
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
    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->authService->resetPassword($request->validated());
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function refresh(\Illuminate\Http\Request $request)
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
