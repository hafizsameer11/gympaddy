<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\UpdateChatMessageRequest;
use App\Services\ChatMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatMessageController extends Controller
{
    protected ChatMessageService $chatMessageService;

    public function __construct(ChatMessageService $chatMessageService)
    {
        $this->chatMessageService = $chatMessageService;
    }

    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    // Optionally filter by receiver_id for 1-on-1 chat
    $params = [];

    if ($request->has('receiver_id')) {
        $params['receiver_id'] = $request->query('receiver_id');
    }

    if ($request->has('conversation_id')) {
        $params['conversation_id'] = $request->query('conversation_id');
    }

    return $this->chatMessageService->index($params);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatMessageRequest $request)
    {
        $data = $request->validated();
        $data['sender_id'] = Auth::id();
        return $this->chatMessageService->store($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatMessage $chatMessage)
    {
        return $this->chatMessageService->show($chatMessage);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatMessage $chatMessage)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatMessageRequest $request, ChatMessage $chatMessage)
    {
        return $this->chatMessageService->update($chatMessage, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatMessage $chatMessage)
    {
        return $this->chatMessageService->destroy($chatMessage);
    }

    /**
     * Display a listing of conversations.
     */
    public function conversations()
    {
        return $this->chatMessageService->listConversations();
    }
}
