<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    public function getAllAdmins()
    {
        try {
            $admins = Admin::all()->map(function ($admin) {
                return [
                    'id' => 'admin_' . $admin->id,
                    'fullName' => $admin->name,
                    'username' => $admin->email,
                    'email' => $admin->email,
                    'role' => 'admin',
                    'status' => 'active',
                    'createdAt' => $admin->created_at->toIso8601String(),
                ];
            });

            return response()->json(['success' => true, 'data' => $admins]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getAdminById($id)
    {
        try {
            $adminId = str_replace('admin_', '', $id);
            $admin = Admin::find($adminId);
            
            if (!$admin) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Admin not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => 'admin_' . $admin->id,
                    'fullName' => $admin->name,
                    'username' => $admin->email,
                    'email' => $admin->email,
                    'role' => 'admin',
                    'permissions' => ['all'],
                    'status' => 'active',
                    'lastLogin' => $admin->updated_at->toIso8601String(),
                    'createdAt' => $admin->created_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function createAdmin(Request $request)
    {
        try {
            $validated = $request->validate([
                'fullName' => 'required|string',
                'username' => 'required|string',
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|string|min:6',
                'role' => 'string',
            ]);

            $admin = Admin::create([
                'name' => $validated['fullName'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json(['success' => true, 'message' => 'Admin created successfully', 'data' => $admin]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateAdmin(Request $request, $id)
    {
        try {
            $adminId = str_replace('admin_', '', $id);
            $admin = Admin::find($adminId);
            
            if (!$admin) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Admin not found']], 404);
            }

            $updateData = [];
            if ($request->has('fullName')) {
                $updateData['name'] = $request->fullName;
            }
            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }

            $admin->update($updateData);
            return response()->json(['success' => true, 'message' => 'Admin updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function deleteAdmin($id)
    {
        try {
            $adminId = str_replace('admin_', '', $id);
            $admin = Admin::find($adminId);
            
            if (!$admin) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Admin not found']], 404);
            }

            $admin->delete();
            return response()->json(['success' => true, 'message' => 'Admin deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
