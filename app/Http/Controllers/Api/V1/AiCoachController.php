<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\FitnessCoach;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiCoachController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'string'],
        ]);

        $agent = new FitnessCoach($request->user());

        if ($request->conversation_id) {
            $agent = $agent->continue($request->conversation_id, as: $request->user());
        } else {
            $agent = $agent->forUser($request->user());
        }

        $response = $agent->prompt($request->message);

        return response()->json([
            'message' => $response->content,
            'conversation_id' => $response->conversationId,
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        $conversations = \DB::table('agent_conversations')
            ->where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json($conversations);
    }
}
