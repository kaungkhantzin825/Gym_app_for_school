<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\FitnessCoach;
use App\Http\Controllers\Controller;
use App\Services\AiKeyRotatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        try {
            $response = AiKeyRotatorService::runWithRotation(function (string $provider, string $key) use ($agent, $request) {
                $agent->setProvider($provider);

                return $agent->prompt($request->message);
            });

            return response()->json([
                'message'         => $response->text,
                'conversation_id' => $response->conversationId,
                'usage'           => [
                    'prompt_tokens'     => $response->usage->promptTokens ?? 0,
                    'completion_tokens' => $response->usage->completionTokens ?? 0,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning("AI Coach chat fallback: {$e->getMessage()}");

            return response()->json([
                'message'         => "I'm having a brief connection delay with the AI service. In the meantime, remember to warm up properly, stay hydrated, and keep your form strict. What workout or nutrition question can I help you plan?",
                'conversation_id' => $request->conversation_id ?? ('local-'.uniqid()),
                'usage'           => [
                    'prompt_tokens'     => 0,
                    'completion_tokens' => 0,
                ],
            ]);
        }
    }

    public function conversations(Request $request): JsonResponse
    {
        $conversations = DB::table('agent_conversations')
            ->where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json($conversations);
    }
}
