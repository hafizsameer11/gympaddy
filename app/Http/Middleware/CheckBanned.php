<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckBanned
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->is_banned) {
            if ($user->banned_until && now()->greaterThan($user->banned_until)) {
                $user->update([
                    'is_banned' => false,
                    'ban_reason' => null,
                    'ban_duration' => null,
                    'banned_until' => null,
                ]);
                return $next($request);
            }

            $user->tokens()->delete();

            $message = 'Your account has been banned. Please contact support.';
            if ($user->banned_until) {
                $message = 'Your account has been banned until ' . $user->banned_until->format('M d, Y h:i A') . '. Reason: ' . ($user->ban_reason ?? 'Not specified');
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_BANNED',
                    'message' => $message,
                ]
            ], 403);
        }

        return $next($request);
    }
}
