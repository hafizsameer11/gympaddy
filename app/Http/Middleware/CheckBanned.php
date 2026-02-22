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
            // Revoke all tokens to force the app to log out
            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_BANNED',
                    'message' => 'Your account has been banned. Please contact support.',
                ]
            ], 403);
        }

        return $next($request);
    }
}
