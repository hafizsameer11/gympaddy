<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function getAllTickets(Request $request)
    {
        try {
            $query = Ticket::with('user:id,username,fullname,email');

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('priority') && $request->priority !== 'all') {
                $query->where('priority', $request->priority);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $tickets = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'tickets' => $tickets->items(),
                    'pagination' => [
                        'currentPage' => $tickets->currentPage(),
                        'totalPages' => $tickets->lastPage(),
                        'totalItems' => $tickets->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getTicketById($id)
    {
        try {
            $ticket = Ticket::with('user:id,username,fullname,email')->find($id);
            if (!$ticket) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ticket not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $ticket->id,
                    'userId' => $ticket->user_id,
                    'userName' => $ticket->user->fullname ?? 'Unknown',
                    'userEmail' => $ticket->user->email ?? '',
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority ?? 'medium',
                    'messages' => [],
                    'createdAt' => $ticket->created_at->toIso8601String(),
                    'lastUpdated' => $ticket->updated_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function createTicket(Request $request)
    {
        try {
            $validated = $request->validate([
                'userId' => 'required',
                'subject' => 'required|string',
                'description' => 'required|string',
                'priority' => 'string',
            ]);

            $ticket = Ticket::create([
                'user_id' => $validated['userId'],
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => $validated['priority'] ?? 'medium',
                'status' => 'open',
            ]);

            return response()->json(['success' => true, 'message' => 'Ticket created successfully', 'data' => $ticket]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateTicket(Request $request, $id)
    {
        try {
            $ticket = Ticket::find($id);
            if (!$ticket) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ticket not found']], 404);
            }
            $ticket->update($request->only(['status', 'priority']));
            return response()->json(['success' => true, 'message' => 'Ticket updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function closeTicket($id)
    {
        try {
            $ticket = Ticket::find($id);
            if (!$ticket) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ticket not found']], 404);
            }
            $ticket->update(['status' => 'closed']);
            return response()->json(['success' => true, 'message' => 'Ticket closed successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
