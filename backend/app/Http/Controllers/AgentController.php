<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Events\NewChatMessage;
use App\Events\AgentStatusChange;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    public function getWaitingSessions(): JsonResponse
    {
        $sessions = ChatSession::where('status', 'waiting_for_agent')
            ->with(['user', 'messages'])
            ->get();

        return response()->json($sessions);
    }

    public function acceptSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)
            ->firstOrFail();

        $session->update([
            'status' => 'with_agent',
            'agent_id' => auth()->id()
        ]);

        // Broadcast agent joined event
        event(new AgentStatusChange($session->session_id, 'with_agent'));

        // Send system message about agent joining
        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'bot',
            'message' => 'An agent has joined the chat.'
        ]);

        event(new NewChatMessage($message));

        return response()->json([
            'message' => 'Session accepted successfully'
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'agent',
            'sender_id' => auth()->id(),
            'message' => $request->message
        ]);

        event(new NewChatMessage($message));

        return response()->json($message);
    }
}
