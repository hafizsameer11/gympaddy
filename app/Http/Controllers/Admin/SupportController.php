<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    private function messageFromTicket(Ticket $ticket): string
    {
        return $ticket->message ?? $ticket->description ?? '';
    }

    private function formatTicket(Ticket $ticket): array
    {
        return [
            'id' => (string) $ticket->id,
            'user_id' => $ticket->user_id,
            'subject' => $ticket->subject,
            'message' => $this->messageFromTicket($ticket),
            'description' => $ticket->description ?? null,
            'admin_reply' => $ticket->admin_reply ?? null,
            'status' => $ticket->status ?? 'open',
            'priority' => $ticket->priority ?? 'medium',
            'created_at' => optional($ticket->created_at)->toIso8601String(),
            'updated_at' => optional($ticket->updated_at)->toIso8601String(),
            'user' => [
                'id' => $ticket->user?->id,
                'username' => $ticket->user?->username ?? '',
                'fullName' => $ticket->user?->fullname ?? '',
                'profile_picture' => $ticket->user?->profile_picture ?? null,
            ],
        ];
    }

    public function getAllTickets(Request $request)
    {
        try {
            $query = Ticket::with('user:id,username,fullname,email,profile_picture');

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('priority') && $request->priority !== 'all') {
                $query->where('priority', $request->priority);
            }

            $tickets = $query->orderBy('created_at', 'desc')->get();

            $formattedTickets = $tickets->map(fn (Ticket $ticket) => $this->formatTicket($ticket))->all();

            return response()->json([
                'success' => true,
                'data' => [
                    'tickets' => $formattedTickets,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getTicketById($id)
    {
        try {
            $ticket = Ticket::with('user:id,username,fullname,email,profile_picture')->find($id);
            if (!$ticket) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ticket not found']], 404);
            }

            $message = $this->messageFromTicket($ticket);
            $messages = [[
                'id' => 'user_' . $ticket->id,
                'text' => $message,
                'isUser' => true,
                'timestamp' => $ticket->created_at->toIso8601String(),
            ]];

            if (!empty($ticket->admin_reply)) {
                $messages[] = [
                    'id' => 'admin_' . $ticket->id,
                    'text' => $ticket->admin_reply,
                    'isUser' => false,
                    'timestamp' => $ticket->updated_at->toIso8601String(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $ticket->id,
                    'userId' => $ticket->user_id,
                    'userName' => $ticket->user->fullname ?? 'Unknown',
                    'userEmail' => $ticket->user->email ?? '',
                    'subject' => $ticket->subject,
                    'message' => $message,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority ?? 'medium',
                    'admin_reply' => $ticket->admin_reply,
                    'messages' => $messages,
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
                'message' => 'required|string',
                'priority' => 'string',
            ]);

            $ticket = Ticket::create([
                'user_id' => $validated['userId'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'description' => $validated['message'],
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
            $ticket->update($request->only(['status', 'priority', 'admin_reply']));
            return response()->json(['success' => true, 'message' => 'Ticket updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function replyToTicket(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reply' => 'required|string',
            ]);

            $ticket = Ticket::find($id);
            if (!$ticket) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ticket not found']], 404);
            }

            $ticket->update([
                'admin_reply' => $validated['reply'],
                'status' => $ticket->status === 'closed' ? 'closed' : 'pending',
            ]);

            return response()->json(['success' => true, 'message' => 'Reply sent successfully']);
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
