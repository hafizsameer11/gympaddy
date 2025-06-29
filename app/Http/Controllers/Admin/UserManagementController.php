<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MarketplaceListingService;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserManagementController extends Controller
{
    protected $userService,$marketplaceListingService;

    public function __construct(UserService $userService, MarketplaceListingService $marketplaceListingService)
    {
        $this->userService = $userService;
    }
    public function index()
    {
        try {
            $data = [
                'title' => 'User Management',
                'count' => $this->userService->userCount(),
                'users' => $this->userService->allUsers()
            ];
            return response()->json(['message' => 'User Management Data Retrieved Successfully', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving user management data', 'error' => $e->getMessage()], 500);
        }
    }
    public function userDetails($id){
    try {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['message' => 'User details retrieved successfully', 'data' => $user]);
    }
    catch (\Exception $e) {
        return response()->json(['message' => 'Error retrieving user details', 'error' => $e->getMessage()], 500);
    }
    }
    public function socialData($id)
    {
        try{
            $data=$this->userService->getUserSocialData($id);
            return response()->json(['message' => 'Social data retrieved successfully', 'data' => $data,'status' => 'success']);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving social data', 'error' => $e->getMessage(),'status' => 'error'], 500);
        }

    }
    public function marketPlaceData($id){

    }
    public function edit(Request $request, $id){

    }
    public function deleteUser($id){

    }

    public function getmarketPlaceListingForUser($userId){
    try {
        $listings = $this->marketplaceListingService->getForUser($userId);
        return response()->json(['message' => 'Marketplace listings retrieved successfully', 'data' => $listings, 'status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error retrieving marketplace listings', 'error' => $e->getMessage()], 500);
    }
}
}
