<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function startSession(Request $request): JsonResponse
    {
        $session = ChatSession::create([
            'user_id' => auth()->id(),
            'session_id' => uniqid('chat_', true),
            'status' => 'active'
        ]);

        return response()->json([
            'session_id' => $session->session_id,
            'message' => 'Chat session started successfully'
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)->firstOrFail();

        // Store user message
        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message' => $request->message
        ]);

        // Process message with AI or route to agent
        $response = $this->processMessage($session, $request->message);

        return response()->json($response);
    }

    private function processMessage(ChatSession $session, string $message): array
    {
        try {
            // Basic keyword-based routing
            $agentTriggers = ['help', 'agent', 'human', 'support', 'confused'];
            $needsAgent = false;

            // Check if message contains any trigger words
            foreach ($agentTriggers as $trigger) {
                if (stripos($message, $trigger) !== false) {
                    $needsAgent = true;
                    break;
                }
            }

            if ($needsAgent) {
                // Update session status and broadcast
                $session->update(['status' => 'waiting_for_agent']);
                event(new \App\Events\AgentStatusChange($session->session_id, 'waiting_for_agent'));

                // Create system message
                $response = ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'sender_type' => 'bot',
                    'message' => "I'll connect you with a human agent who can better assist you. Please wait a moment."
                ]);

                event(new \App\Events\NewChatMessage($response));

                return [
                    'type' => 'agent_handover',
                    'message' => 'Connecting you with an agent...'
                ];
            }

            // AI handles the message
            $response = ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender_type' => 'bot',
                'message' => $this->generateAIResponse($message)
            ]);

            event(new \App\Events\NewChatMessage($response));

            return [
                'type' => 'bot_response',
                'message' => $response->message
            ];
        } catch (\Exception $e) {
            \Log::error('Error processing message: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generateAIResponse(string $message): string
    {
        // Simple response generation - This can be replaced with a more sophisticated AI service
        $greetings = ['hi', 'hello', 'hey'];
        $message = strtolower($message);

        if (str_contains($message, 'bye')) {
            return "Goodbye! Feel free to come back if you have more questions.";
        }

        foreach ($greetings as $greeting) {
            if (str_contains($message, $greeting)) {
                return "Hello! How can I assist you today?";
            }
        }

        // Default response with context
        return "I understand your message about '" . substr($message, 0, 30) .
               "'. How can I help you further with that?";

            return [
                'type' => 'agent_handover',
                'message' => 'Connecting you with an agent...'
            ];
        }
    }

    public function getMessages(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)
            ->firstOrFail();

        $messages = $session->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function endSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)
            ->firstOrFail();

        $session->update(['status' => 'closed']);

        return response()->json([
            'message' => 'Chat session ended successfully'
        ]);
    }
}
