<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\MarketplaceListingService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserManagementController extends Controller
{
    protected $userService, $marketplaceListingService, $transactionService;

    public function __construct(UserService $userService, MarketplaceListingService $marketplaceListingService, TransactionService $transactionService)
    {
        $this->userService = $userService;
        $this->marketplaceListingService = $marketplaceListingService;
        $this->transactionService = $transactionService;
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
    public function userDetails($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            return response()->json(['message' => 'User details retrieved successfully', 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving user details', 'error' => $e->getMessage()], 500);
        }
    }
    public function socialData($id)
    {
        try {
            $data = $this->userService->getUserSocialData($id);
            return response()->json(['message' => 'Social data retrieved successfully', 'data' => $data, 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving social data', 'error' => $e->getMessage(), 'status' => 'error'], 500);
        }
    }
    private function formatUser(\App\Models\User $user): array
    {
        return [
            'id'             => $user->id,
            'fullName'       => $user->fullname ?? '',
            'username'       => $user->username ?? '',
            'email'          => $user->email ?? '',
            'phoneNumber'    => $user->phone ?? '',
            'status'         => $user->status ?? 'offline',
            'lastLogin'      => $user->updated_at?->toISOString() ?? null,
            'dateRegistered' => $user->created_at?->toISOString() ?? null,
            'profile_picture'=> $user->profile_picture,
            'gender'         => $user->gender ?? null,
            'age'            => $user->age ?? null,
            'role'           => $user->role ?? 'user',
        ];
    }

    public function getAllUsers(Request $request)
    {
        try {
            $query = \App\Models\User::query();

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('fullname', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $page  = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $users = $query->paginate($limit, ['*'], 'page', $page);

            $formattedUsers = collect($users->items())
                ->map(fn($u) => $this->formatUser($u))
                ->all();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $formattedUsers,
                    'pagination' => [
                        'currentPage' => $users->currentPage(),
                        'totalPages'  => $users->lastPage(),
                        'totalItems'  => $users->total(),
                        'itemsPerPage'=> $users->perPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserByUsername($username)
    {
        try {
            $user = \App\Models\User::where('username', $username)->first();
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found']], 404);
            }
            return response()->json(['success' => true, 'data' => $this->formatUser($user)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function createUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'fullName' => 'required|string',
                'username' => 'required|string|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'phoneNumber' => 'required|string',
                'gender' => 'required|string',
                'age' => 'required|integer',
                'password' => 'required|string|min:6',
            ]);

            $user = \App\Models\User::create([
                'fullname' => $validated['fullName'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phoneNumber'],
                'gender' => $validated['gender'],
                'age' => $validated['age'],
                'password' => bcrypt($validated['password']),
            ]);

            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_picture = $path;
                $user->save();
            }

            return response()->json(['success' => true, 'message' => 'User created successfully', 'data' => ['id' => $user->id, 'username' => $user->username]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $user = \App\Models\User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found']], 404);
            }

            $user->update($request->only(['fullname', 'phone', 'age', 'gender']));
            return response()->json(['success' => true, 'message' => 'User updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = \App\Models\User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found']], 404);
            }
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function banUser(Request $request, $id)
    {
        try {
            $user = \App\Models\User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found']], 404);
            }

            $user->update([
                'is_banned' => true,
                'ban_reason' => $request->reason,
                'ban_duration' => $request->duration,
            ]);

            return response()->json(['success' => true, 'message' => 'User banned successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function unbanUser($id)
    {
        try {
            $user = \App\Models\User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found']], 404);
            }

            $user->update([
                'is_banned' => false,
                'ban_reason' => null,
                'ban_duration' => null,
            ]);

            return response()->json(['success' => true, 'message' => 'User unbanned successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserStats()
    {
        try {
            $totalUsers = \App\Models\User::count();
            $activeUsers = \App\Models\User::where('status', 'online')->count();
            $newUsersToday = \App\Models\User::whereDate('created_at', today())->count();
            $onlineUsers = \App\Models\User::where('status', 'online')->count();
            $bannedUsers = \App\Models\User::where('is_banned', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalUsers' => $totalUsers,
                    'activeUsers' => $activeUsers,
                    'newUsersToday' => $newUsersToday,
                    'onlineUsers' => $onlineUsers,
                    'bannedUsers' => $bannedUsers,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserStatsBySection()
    {
        try {
            $totalUsers       = \App\Models\User::count();
            $socialUsers      = \App\Models\Post::distinct('user_id')->count('user_id');
            $connectUsers     = \App\Models\UserProfile::distinct('user_id')->count('user_id');
            $marketplaceUsers = \App\Models\MarketplaceListing::distinct('user_id')->count('user_id');
            $gymHubUsers      = \App\Models\Business::distinct('user_id')->count('user_id');

            return response()->json([
                'success' => true,
                'data' => [
                    'totalUsers'       => $totalUsers,
                    'socialUsers'      => $socialUsers,
                    'connectUsers'     => $connectUsers,
                    'marketplaceUsers' => $marketplaceUsers,
                    'gymHubUsers'      => $gymHubUsers,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]
            ], 500);
        }
    }

    public function getmarketPlaceListingForUser($userId)
    {
        try {
            $listings = $this->marketplaceListingService->getForUser($userId);
            return response()->json(['message' => 'Marketplace listings retrieved successfully', 'data' => $listings, 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving marketplace listings', 'error' => $e->getMessage()], 500);
        }
    }
    public function getUserChats($id)
    {
        $conVersations = Conversation::where('user1_id', $id)
            ->orWhere('user2_id', $id)
            ->with(['user1:id,username,fullname,profile_picture', 'user2:id,username,fullname,profile_picture', 'messages'])
            ->get();

        return response()->json(['message' => 'User chats retrieved successfully', 'data' => $conVersations, 'status' => 'success']);
    }
    public function getUserTransactions($id)
    {
        try {
            $transactions = $this->transactionService->getForUser($id);
            return response()->json(['message' => 'User transactions retrieved successfully', 'data' => $transactions, 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving user transactions', 'error' => $e->getMessage(), 'status' => 'error'], 500);
        }
    }
}
