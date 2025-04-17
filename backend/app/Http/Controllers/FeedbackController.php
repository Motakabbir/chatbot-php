<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)->firstOrFail();

        $feedback = Feedback::create([
            'chat_session_id' => $session->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'feedback' => $feedback
        ]);
    }

    public function getFeedbackStats(): JsonResponse
    {
        $stats = [
            'average_rating' => Feedback::avg('rating'),
            'total_feedback' => Feedback::count(),
            'rating_distribution' => Feedback::selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating')
                ->get()
        ];

        return response()->json($stats);
    }
}
