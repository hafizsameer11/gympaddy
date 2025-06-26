<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\PasswordOtp;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function login($validated)
    {
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        }
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register($validated)
    {
        $user = User::create([
            'username' => $validated['username'],
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'password' => Hash::make($validated['password']),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function forgotPassword($validated)
    {
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $otpRecord = PasswordOtp::generateOtp($validated['email']);

        // Send OTP via email
        Mail::raw("Your password reset OTP is: {$otpRecord->otp}\n\nThis OTP will expire in 10 minutes.", function ($message) use ($validated) {
            $message->to($validated['email'])
                ->subject('Password Reset OT`P - GymPaddy');
        });

        return response()->json([
            'otp' => $otpRecord->otp, // ✅ Use 'otp' instead of 'opt'
            'message' => 'OTP sent to your email address',
            'email' => $validated['email']
        ]);
    }

    public function verifyOtp($validated)
    {
        $isValid = PasswordOtp::verifyOtp($validated['email'], $validated['otp']);

        if ($isValid) {
            return response()->json([
                'message' => 'OTP verified successfully',
                'email' => $validated['email']
            ]);
        }

        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }

    public function resetPassword($validated)
    {
        // For OTP-based reset, we first verify the OTP again as a security measure
        if (isset($validated['otp'])) {
            $isValid = PasswordOtp::verifyOtp($validated['email'], $validated['otp']);

            if (!$isValid) {
                return response()->json(['message' => 'Invalid or expired OTP'], 400);
            }
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json(['message' => 'Password reset successful']);
    }
}
