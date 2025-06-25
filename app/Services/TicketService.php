<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
    public function index()
    {
        return Ticket::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $ticket = Ticket::create($data);
        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        return $ticket;
    }

    public function update(Ticket $ticket, $validated)
    {
        $ticket->update($validated);
        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
