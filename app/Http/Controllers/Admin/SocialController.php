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
            $query = Post::with('user:id,username,fullname,profile_picture');

            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            if ($request->has('userId')) {
                $query->where('user_id', $request->userId);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $posts = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $formattedPosts = $posts->getCollection()->map(function ($post) {
                return [
                    'id' => $post->id,
                    'userId' => $post->user_id,
                    'userName' => $post->user->fullname ?? 'Unknown',
                    'userAvatar' => $post->user->profile_picture ?? null,
                    'content' => $post->content,
                    'images' => $post->media ? json_decode($post->media) : [],
                    'likes' => $post->likes_count ?? 0,
                    'comments' => $post->comments_count ?? 0,
                    'shares' => $post->shares_count ?? 0,
                    'createdAt' => $post->created_at->toIso8601String(),
                ];
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

    public function getPostById($id)
    {
        try {
            $post = Post::with('user:id,username,fullname,profile_picture')->find($id);
            if (!$post) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Post not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $post->id,
                    'userId' => $post->user_id,
                    'userName' => $post->user->fullname ?? 'Unknown',
                    'content' => $post->content,
                    'images' => $post->media ? json_decode($post->media) : [],
                    'likes' => $post->likes_count ?? 0,
                    'comments' => $post->comments_count ?? 0,
                    'shares' => $post->shares_count ?? 0,
                    'createdAt' => $post->created_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserPosts($userId)
    {
        try {
            $posts = Post::where('user_id', $userId)
                ->with('user:id,username,fullname,profile_picture')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $posts
            ]);
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
                    return [
                        'id' => $story->id,
                        'userId' => $story->user_id,
                        'userName' => $story->user->fullname ?? 'Unknown',
                        'content' => $story->content ?? '',
                        'createdAt' => $story->created_at->toIso8601String(),
                        'expiresAt' => $story->created_at->addHours(24)->toIso8601String(),
                    ];
                });

            return response()->json(['success' => true, 'data' => $statuses]);
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
            $query = LiveStream::with('user:id,username,fullname,profile_picture');

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $streams = $query->orderBy('created_at', 'desc')->get()->map(function ($stream) {
                return [
                    'id' => $stream->id,
                    'userId' => $stream->user_id,
                    'userName' => $stream->user->fullname ?? 'Unknown',
                    'title' => $stream->title ?? 'Live Stream',
                    'viewers' => $stream->viewers_count ?? 0,
                    'status' => $stream->status ?? 'active',
                    'startedAt' => $stream->created_at->toIso8601String(),
                ];
            });

            return response()->json(['success' => true, 'data' => $streams]);
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
            $activeLiveStreams = LiveStream::where('status', 'active')->count();
            $postsToday = Post::whereDate('created_at', today())->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalPosts' => $totalPosts,
                    'totalStatuses' => $totalStatuses,
                    'totalLiveStreams' => $totalLiveStreams,
                    'activeLiveStreams' => $activeLiveStreams,
                    'postsToday' => $postsToday,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
