<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    private function formatAdmin(Admin $admin): array
    {
        return [
            'id'              => 'admin_' . $admin->id,
            'fullName'        => $admin->name,
            'username'        => $admin->email,
            'email'           => $admin->email,
            'role'            => 'admin',
            'status'          => 'active',
            'profile_picture' => null,
            'gender'          => '',
            'createdAt'       => $admin->created_at->toIso8601String(),
            'date'            => $admin->created_at->format('d/m/y - h:i A'),
            'lastLogin'       => $admin->updated_at->toIso8601String(),
        ];
    }

    public function getAllAdmins()
    {
        try {
            $admins = Admin::orderBy('created_at', 'desc')
                ->get()
                ->map(fn($admin) => $this->formatAdmin($admin));

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

            return response()->json(['success' => true, 'data' => $this->formatAdmin($admin)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function createAdmin(Request $request)
    {
        try {
            $validated = $request->validate([
                'fullName' => 'required|string',
                'email'    => 'required|email|unique:admins,email',
                'password' => 'required|string|min:6',
            ]);

            $admin = Admin::create([
                'name'     => $validated['fullName'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully',
                'data'    => $this->formatAdmin($admin),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
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
            if ($request->has('fullName')) $updateData['name']  = $request->fullName;
            if ($request->has('email'))    $updateData['email'] = $request->email;
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $admin->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully',
                'data'    => $this->formatAdmin($admin->fresh()),
            ]);
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
