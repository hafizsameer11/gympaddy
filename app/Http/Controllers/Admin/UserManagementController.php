<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Ticket;
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
            'is_banned'      => (bool) $user->is_banned,
            'ban_reason'     => $user->ban_reason,
            'banned_until'   => $user->banned_until?->toIso8601String(),
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

            $section = $request->get('section');
            if ($section && in_array($section, ['social', 'marketplace', 'connect', 'gym'], true)) {
                if ($section === 'social') {
                    $query->whereHas('posts');
                } elseif ($section === 'marketplace') {
                    $query->whereHas('marketplaceListings');
                } elseif ($section === 'connect') {
                    $query->whereHas('profile');
                } elseif ($section === 'gym') {
                    $query->whereHas('businesses');
                }
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
                'profile_picture' => 'nullable|image|max:5120',
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
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

            $updateData = [];

            if ($request->has('fullName'))    $updateData['fullname'] = $request->fullName;
            if ($request->has('username')) {
                $existing = \App\Models\User::where('username', $request->username)->where('id', '!=', $id)->first();
                if ($existing) {
                    return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Username is already taken']], 422);
                }
                $updateData['username'] = $request->username;
            }
            if ($request->has('email')) {
                $existing = \App\Models\User::where('email', $request->email)->where('id', '!=', $id)->first();
                if ($existing) {
                    return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Email is already taken']], 422);
                }
                $updateData['email'] = $request->email;
            }
            if ($request->has('phoneNumber')) $updateData['phone'] = $request->phoneNumber;
            if ($request->has('age'))         $updateData['age'] = $request->age;
            if ($request->has('gender'))      $updateData['gender'] = $request->gender;

            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $updateData['profile_picture'] = $path;
            }

            $user->update($updateData);
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data'    => $this->formatUser($user),
            ]);
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

            \Illuminate\Support\Facades\DB::beginTransaction();

            try {
                // Clean up tables without cascade-delete foreign keys
                \Illuminate\Support\Facades\DB::table('gifts')
                    ->where('from_user_id', $id)
                    ->orWhere('to_user_id', $id)
                    ->delete();

                \Illuminate\Support\Facades\DB::table('transactions')
                    ->where('related_user_id', $id)
                    ->delete();

                $user->tokens()->delete();
                $user->delete();

                \Illuminate\Support\Facades\DB::commit();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                throw $e;
            }

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

            $duration = (int) $request->duration;
            $unit = $request->unit ?? 'days';
            $bannedUntil = now();

            switch ($unit) {
                case 'minutes': $bannedUntil = $bannedUntil->addMinutes($duration); break;
                case 'hours':   $bannedUntil = $bannedUntil->addHours($duration); break;
                case 'days':    $bannedUntil = $bannedUntil->addDays($duration); break;
                case 'weeks':   $bannedUntil = $bannedUntil->addWeeks($duration); break;
                case 'months':  $bannedUntil = $bannedUntil->addMonths($duration); break;
                case 'years':   $bannedUntil = $bannedUntil->addYears($duration); break;
                default:        $bannedUntil = $bannedUntil->addDays($duration); break;
            }

            $user->update([
                'is_banned' => true,
                'ban_reason' => $request->reason,
                'ban_duration' => $duration . ' ' . $unit,
                'banned_until' => $bannedUntil,
            ]);

            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => "User banned for {$duration} {$unit}",
                'data' => [
                    'banned_until' => $bannedUntil->toIso8601String(),
                    'ban_reason' => $request->reason,
                ],
            ]);
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
                'banned_until' => null,
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
        try {
            $conversations = Conversation::where('user1_id', $id)
                ->orWhere('user2_id', $id)
                ->with(['user1:id,username,fullname,profile_picture', 'user2:id,username,fullname,profile_picture', 'messages'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($conv) use ($id) {
                    $otherUser = $conv->user1_id == $id ? $conv->user2 : $conv->user1;
                    $lastMessage = $conv->messages->sortByDesc('created_at')->first();

                    return [
                        'id'              => $conv->id,
                        'type'            => 'chat',
                        'otherUserId'     => $otherUser->id ?? null,
                        'otherUserName'   => $otherUser->fullname ?? 'Unknown',
                        'otherUsername'   => $otherUser->username ?? '',
                        'otherUserAvatar' => $otherUser->profile_picture ?? null,
                        'lastMessage'     => $lastMessage->message ?? $lastMessage->content ?? '',
                        'lastMessageAt'   => $lastMessage ? $lastMessage->created_at->toIso8601String() : $conv->updated_at->toIso8601String(),
                        'messageCount'    => $conv->messages->count(),
                        'date'            => $conv->updated_at->format('d/m/y'),
                    ];
                });

            $tickets = Ticket::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id'              => 'ticket_' . $ticket->id,
                        'type'            => 'support',
                        'otherUserId'     => null,
                        'otherUserName'   => 'Support Team',
                        'otherUsername'   => 'support',
                        'otherUserAvatar' => null,
                        'lastMessage'     => $ticket->message ?? $ticket->description ?? $ticket->subject ?? '',
                        'lastMessageAt'   => $ticket->updated_at->toIso8601String(),
                        'messageCount'    => 1,
                        'date'            => $ticket->created_at->format('d/m/y'),
                        'subject'         => $ticket->subject ?? '',
                        'status'          => $ticket->status ?? 'open',
                    ];
                });

            $allChats = $conversations->concat($tickets)
                ->sortByDesc('lastMessageAt')
                ->values();

            return response()->json(['success' => true, 'data' => $allChats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getConversationMessages($conversationId)
    {
        try {
            $conversation = Conversation::with([
                'user1:id,username,fullname,profile_picture',
                'user2:id,username,fullname,profile_picture',
                'messages' => function ($q) {
                    $q->orderBy('created_at', 'asc');
                },
                'messages.sender:id,username,fullname,profile_picture',
            ])->find($conversationId);

            if (!$conversation) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Conversation not found']], 404);
            }

            $messages = $conversation->messages->map(function ($msg) {
                return [
                    'id'        => $msg->id,
                    'senderId'  => $msg->sender_id,
                    'senderName'=> $msg->sender->fullname ?? $msg->sender->username ?? 'Unknown',
                    'senderAvatar' => $msg->sender->profile_picture ?? null,
                    'message'   => $msg->message ?? '',
                    'image'     => $msg->image ?? null,
                    'read'      => (bool) $msg->read,
                    'createdAt' => $msg->created_at->toIso8601String(),
                    'time'      => $msg->created_at->format('h:i A'),
                    'date'      => $msg->created_at->format('M d, Y'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $conversation->id,
                    'user1' => [
                        'id' => $conversation->user1->id ?? null,
                        'name' => $conversation->user1->fullname ?? 'Unknown',
                        'username' => $conversation->user1->username ?? '',
                        'avatar' => $conversation->user1->profile_picture ?? null,
                    ],
                    'user2' => [
                        'id' => $conversation->user2->id ?? null,
                        'name' => $conversation->user2->fullname ?? 'Unknown',
                        'username' => $conversation->user2->username ?? '',
                        'avatar' => $conversation->user2->profile_picture ?? null,
                    ],
                    'messages' => $messages,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getTicketDetails($ticketId)
    {
        try {
            $ticket = Ticket::with('user:id,username,fullname,profile_picture')->find($ticketId);

            if (!$ticket) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ticket not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'message' => $ticket->message ?? $ticket->description,
                    'status' => $ticket->status,
                    'user' => [
                        'id' => $ticket->user->id ?? null,
                        'name' => $ticket->user->fullname ?? 'Unknown',
                        'username' => $ticket->user->username ?? '',
                        'avatar' => $ticket->user->profile_picture ?? null,
                    ],
                    'createdAt' => $ticket->created_at->toIso8601String(),
                    'date' => $ticket->created_at->format('M d, Y'),
                    'time' => $ticket->created_at->format('h:i A'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
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
