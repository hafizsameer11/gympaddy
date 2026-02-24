<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TicketService
{
    private function messageFromTicket(Ticket $ticket): string
    {
        return $ticket->message ?? $ticket->description ?? '';
    }

    private function mapTicket(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'subject' => $ticket->subject,
            'message' => $this->messageFromTicket($ticket),
            'description' => $ticket->description ?? null,
            'admin_reply' => $ticket->admin_reply ?? null,
            'priority' => $ticket->priority ?? 'medium',
            'status' => $ticket->status ?? 'open',
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at,
        ];
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 200);
        }

        return Ticket::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (Ticket $ticket) => $this->mapTicket($ticket));
    }

    public function store($user, $validated)
    {
        $message = $validated['message'] ?? $validated['description'] ?? '';
        $data = [
            'subject' => $validated['subject'],
            'status' => 'open',
        ];

        if (Schema::hasColumn('tickets', 'message')) {
            $data['message'] = $message;
        }
        if (Schema::hasColumn('tickets', 'description')) {
            $data['description'] = $message;
        }
        if (Schema::hasColumn('tickets', 'priority')) {
            $data['priority'] = $validated['priority'] ?? 'medium';
        }

        $data['user_id'] = $user->id;
        $ticket = Ticket::create($data);
        return response()->json($this->mapTicket($ticket), 201);
    }

    public function show(Ticket $ticket)
    {
        return $this->mapTicket($ticket);
    }

    public function update(Ticket $ticket, $validated)
    {
        if (isset($validated['message'])) {
            if (Schema::hasColumn('tickets', 'message')) {
                $validated['message'] = $validated['message'];
            }
            if (Schema::hasColumn('tickets', 'description')) {
                $validated['description'] = $validated['message'];
            }
        }

        $ticket->update($validated);
        return response()->json($this->mapTicket($ticket->fresh()));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
