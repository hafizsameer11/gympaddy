<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;

class AdminUserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        // Optionally add middleware to restrict to admin
        $this->middleware(function ($request, $next) {
            if (!$request->user() || $request->user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
        $this->userService = $userService;
    }

    public function index()
    {
        return $this->userService->index();
    }

    public function show($id)
    {
        return $this->userService->show($id);
    }

    public function store(StoreUserRequest $request)
    {
        return $this->userService->store($request->validated());
    }

    public function update(UpdateUserRequest $request, $id)
    {
        return $this->userService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->userService->destroy($id);
    }
 
}


  

