<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // Example dashboard data, adjust as needed
        return response()->json([
            'message' => 'Admin dashboard',
            'user' => $request->user(),
        ]);
    }
}
