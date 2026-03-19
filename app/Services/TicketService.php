<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TicketService
{
    private function notifyAdmins(string $title, string $body, string $type = 'support'): void
    {
        // Notify all admin users so the dashboard badge can reflect new support messages.
        $adminIds = User::where('role', 'admin')->pluck('id');

        foreach ($adminIds as $adminId) {
            Notification::create([
                'user_id' => (int) $adminId,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'status' => 'sent',
                'is_read' => false,
            ]);
        }
    }

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

        $subject = (string) ($validated['subject'] ?? $ticket->subject ?? 'Support');
        $this->notifyAdmins(
            'New Support Ticket',
            trim($subject . ': ' . $message),
            'support'
        );

        return response()->json($this->mapTicket($ticket), 201);
    }

    public function show(Ticket $ticket)
    {
        return $this->mapTicket($ticket);
    }

    public function update(Ticket $ticket, $validated)
    {
        $shouldNotifyAdmins = array_key_exists('message', $validated) || array_key_exists('subject', $validated);
        $incomingMessage = (string) ($validated['message'] ?? '');
        $incomingSubject = (string) ($validated['subject'] ?? '');

        if (isset($validated['message'])) {
            if (Schema::hasColumn('tickets', 'message')) {
                $validated['message'] = $validated['message'];
            }
            if (Schema::hasColumn('tickets', 'description')) {
                $validated['description'] = $validated['message'];
            }
        }

        $ticket->update($validated);

        if ($shouldNotifyAdmins) {
            $fresh = $ticket->fresh();
            $subject = $incomingSubject !== '' ? $incomingSubject : (string) ($fresh->subject ?? 'Support');
            $message = $incomingMessage !== '' ? $incomingMessage : $this->messageFromTicket($fresh);

            $this->notifyAdmins(
                'New Support Message',
                trim($subject . ': ' . $message),
                'support'
            );
        }

        return response()->json($this->mapTicket($ticket->fresh()));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
