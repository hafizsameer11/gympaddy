<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Story;
use App\Models\LiveStream;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function getAllPosts(Request $request)
    {
        try {
            $query = Post::with(['user:id,username,fullname,profile_picture', 'media'])
                ->withCount(['likes', 'allComments as comments_count', 'shares']);

            if ($request->has('type') && $request->type !== 'all') {
                $query->where('media_type', $request->type);
            }

            if ($request->has('userId')) {
                $query->where('user_id', $request->userId);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $posts = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $formattedPosts = $posts->getCollection()->map(function ($post) {
                return $this->formatPost($post);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'posts' => $formattedPosts,
                    'pagination' => [
                        'currentPage' => $posts->currentPage(),
                        'totalPages' => $posts->lastPage(),
                        'totalItems' => $posts->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    private function formatPost(Post $post): array
    {
        $user = $post->user;

        // Collect images: first from PostMedia relation, then fall back to media_url column
        $images = [];
        if ($post->relationLoaded('media') && $post->media->isNotEmpty()) {
            $images = $post->media->pluck('file_path')->toArray();
        }
        if (empty($images) && !empty($post->media_url)) {
            $images = [$post->media_url];
        }

        $hasMedia = !empty($images);
        $mediaType = $post->media_type ?? 'none';
        $postType = $hasMedia ? 'Image' : ($mediaType === 'none' ? 'Text' : ucfirst($mediaType));

        return [
            'id'             => $post->id,
            'userId'         => $post->user_id,
            'userName'       => $user->fullname ?? 'Unknown',
            'username'       => $user->username ?? '',
            'userAvatar'     => $user->profile_picture ?? null,
            'content'        => $post->content ?? '',
            'mediaUrl'       => $images[0] ?? $post->media_url,
            'mediaType'      => $mediaType,
            'images'         => $images,
            'likes'          => (int) ($post->likes_count ?? 0),
            'comments'       => (int) ($post->comments_count ?? 0),
            'shares'         => (int) ($post->shares_count ?? 0),
            'isBoosted'      => (bool) $post->is_boosted,
            'boostStatus'    => $post->is_boosted ? 'Yes' : 'No',
            'postType'       => $postType,
            'createdAt'      => $post->created_at->toIso8601String(),
            'date'           => $post->created_at->format('d/m/y'),
        ];
    }

    public function getPostById($id)
    {
        try {
            $post = Post::with(['user:id,username,fullname,profile_picture', 'media'])
                ->withCount(['likes', 'allComments as comments_count', 'shares'])
                ->find($id);
            if (!$post) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Post not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatPost($post)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserPosts($userId)
    {
        try {
            $posts = Post::where('user_id', $userId)
                ->with(['user:id,username,fullname,profile_picture', 'media'])
                ->withCount(['likes', 'allComments as comments_count', 'shares'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($post) => $this->formatPost($post));

            return response()->json([
                'success' => true,
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserStatuses($userId)
    {
        try {
            $statuses = Story::where('user_id', $userId)
                ->with('user:id,username,fullname,profile_picture')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($story) {
                    $user = $story->user;
                    $expiresAt = $story->expires_at
                        ? \Carbon\Carbon::parse($story->expires_at)
                        : $story->created_at->copy()->addHours(24);
                    $isRunning = now()->lt($expiresAt);

                    return [
                        'id'              => $story->id,
                        'userId'          => $story->user_id,
                        'fullName'        => $user->fullname ?? 'Unknown',
                        'username'        => $user->username ?? '',
                        'profile_picture' => $user->profile_picture ?? null,
                        'postType'        => ucfirst($story->media_type ?? 'photo'),
                        'postImage'       => $story->media_url,
                        'caption'         => $story->caption ?? '',
                        'views'           => 0,
                        'likes'           => 0,
                        'status'          => $isRunning ? 'Running' : 'Ended',
                        'date'            => $story->created_at->format('d/m/y'),
                        'createdAt'       => $story->created_at->toIso8601String(),
                        'expiresAt'       => $expiresAt->toIso8601String(),
                    ];
                });

            return response()->json(['success' => true, 'data' => $statuses]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserLiveStreams($userId)
    {
        try {
            $streams = LiveStream::where('user_id', $userId)
                ->with('user:id,username,fullname,profile_picture')
                ->withCount('audiences')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($stream) {
                    $user = $stream->user;
                    return [
                        'id'              => $stream->id,
                        'userId'          => $stream->user_id,
                        'fullName'        => $user->fullname ?? 'Unknown',
                        'username'        => $user->username ?? '',
                        'profile_picture' => $user->profile_picture ?? null,
                        'postType'        => 'Live',
                        'postImage'       => $user->profile_picture ?? null,
                        'title'           => $stream->title ?? 'Live Stream',
                        'views'           => (int) ($stream->audiences_count ?? 0),
                        'likes'           => 0,
                        'earned'          => '—',
                        'status'          => $stream->is_active ? 'Running' : 'Ended',
                        'date'            => $stream->created_at->format('d/m/y'),
                        'createdAt'       => $stream->created_at->toIso8601String(),
                    ];
                });

            return response()->json(['success' => true, 'data' => $streams]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function deletePost($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Post not found']], 404);
            }
            $post->delete();
            return response()->json(['success' => true, 'message' => 'Post deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getAllStatuses()
    {
        try {
            $statuses = Story::with('user:id,username,fullname,profile_picture')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($story) {
                    $user = $story->user;
                    $expiresAt = $story->expires_at
                        ? \Carbon\Carbon::parse($story->expires_at)
                        : $story->created_at->copy()->addHours(24);
                    $isRunning = now()->lt($expiresAt);

                    return [
                        'id'              => $story->id,
                        'userId'          => $story->user_id,
                        'fullName'        => $user->fullname ?? 'Unknown',
                        'username'        => $user->username ?? '',
                        'profile_picture' => $user->profile_picture ?? null,
                        'postType'        => ucfirst($story->media_type ?? 'photo'),
                        'postImage'       => $story->media_url,
                        'caption'         => $story->caption ?? '',
                        'views'           => 0,
                        'likes'           => 0,
                        'status'          => $isRunning ? 'Running' : 'Ended',
                        'date'            => $story->created_at->format('d/m/y'),
                        'createdAt'       => $story->created_at->toIso8601String(),
                        'expiresAt'       => $expiresAt->toIso8601String(),
                    ];
                });

            return response()->json(['success' => true, 'data' => ['statuses' => $statuses]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function deleteStatus($id)
    {
        try {
            $status = Story::find($id);
            if (!$status) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Status not found']], 404);
            }
            $status->delete();
            return response()->json(['success' => true, 'message' => 'Status deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getAllLiveStreams(Request $request)
    {
        try {
            $query = LiveStream::with('user:id,username,fullname,profile_picture')
                ->withCount('audiences');

            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'ended') {
                    $query->where('is_active', false);
                }
            }

            $streams = $query->orderBy('created_at', 'desc')->get()->map(function ($stream) {
                $user = $stream->user;
                return [
                    'id'              => $stream->id,
                    'userId'          => $stream->user_id,
                    'fullName'        => $user->fullname ?? 'Unknown',
                    'username'        => $user->username ?? '',
                    'profile_picture' => $user->profile_picture ?? null,
                    'postType'        => 'Live',
                    'postImage'       => $user->profile_picture ?? null,
                    'title'           => $stream->title ?? 'Live Stream',
                    'views'           => (int) ($stream->audiences_count ?? 0),
                    'likes'           => 0,
                    'earned'          => '—',
                    'status'          => $stream->is_active ? 'Running' : 'Ended',
                    'date'            => $stream->created_at->format('d/m/y'),
                    'createdAt'       => $stream->created_at->toIso8601String(),
                ];
            });

            return response()->json(['success' => true, 'data' => ['liveStreams' => $streams]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function endLiveStream($id)
    {
        try {
            $stream = LiveStream::find($id);
            if (!$stream) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Live stream not found']], 404);
            }
            $stream->update(['status' => 'ended']);
            return response()->json(['success' => true, 'message' => 'Live stream ended successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getSocialStats()
    {
        try {
            $totalPosts = Post::count();
            $totalStatuses = Story::count();
            $totalLiveStreams = LiveStream::count();
            $activeLiveStreams = LiveStream::where('is_active', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalPosts'       => $totalPosts,
                    'totalStatuses'    => $totalStatuses,
                    'liveStreams'      => $totalLiveStreams,
                    'activeLiveStreams' => $activeLiveStreams,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
