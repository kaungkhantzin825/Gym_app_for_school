<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\FitnessCoach;
use App\Http\Controllers\Controller;
use App\Services\AiKeyRotatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Image;

/**
 * The AI coach chat, backed by `agent_conversations` and
 * `agent_conversation_messages` (written by laravel/ai's RememberConversation
 * middleware).
 *
 * The app can list past conversations, reopen one and keep going, and delete
 * it - the history lives on the account, not on the phone, so it survives a
 * reinstall and is the same on every device.
 */
class AiCoachController extends Controller
{
    /**
     * Send a message and get the coach's reply.
     */
    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:2000', 'required_without:image'],
            'conversation_id' => ['nullable', 'uuid'],
            'image' => ['nullable', 'image', 'max:10240'],
        ]);

        $user = $request->user();
        $conversationId = $data['conversation_id'] ?? null;

        // A conversation id is a client-supplied string, so it has to be
        // checked against the account before the agent is pointed at it.
        if ($conversationId !== null) {
            $owned = DB::table('agent_conversations')
                ->where('id', $conversationId)
                ->where('user_id', $user->id)
                ->exists();

            abort_unless($owned, 404, 'Conversation not found.');
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('coach-images', 'public')
            : null;

        $message = trim((string) ($data['message'] ?? ''));

        if ($message === '') {
            $message = 'Please look at this photo and tell me what stands out about my form, meal or progress.';
        }

        $agent = new FitnessCoach($user);

        $agent = $conversationId !== null
            ? $agent->continue($conversationId, as: $user)
            : $agent->forUser($user);

        $attachments = $imagePath !== null
            ? [Image::fromStorage($imagePath, 'public')]
            : [];

        try {
            $response = AiKeyRotatorService::runWithRotation(function (string $provider, string $key) use ($agent, $message, $attachments) {
                $agent->setProvider($provider);

                return $agent->prompt($message, $attachments);
            });
        } catch (\Throwable $e) {
            Log::warning("AI Coach chat fallback: {$e->getMessage()}");

            return response()->json([
                'message' => "I'm having a brief connection delay with the AI service. In the meantime, remember to warm up properly, stay hydrated, and keep your form strict. What workout or nutrition question can I help you plan?",
                // No invented id here: handing back a fake one made every
                // later turn try to continue a conversation that never
                // existed, so the chat stayed broken for the rest of the day.
                'conversation_id' => $conversationId,
                'title' => null,
                'usage' => [
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                ],
            ]);
        }

        $savedId = $response->conversationId;

        if ($savedId !== null) {
            // laravel/ai never touches updated_at after the first message, so
            // the list would keep sorting by creation instead of activity.
            DB::table('agent_conversations')
                ->where('id', $savedId)
                ->update(['updated_at' => now()]);
        }

        return response()->json([
            'message' => $response->text,
            'conversation_id' => $savedId,
            'title' => $savedId !== null
                ? DB::table('agent_conversations')->where('id', $savedId)->value('title')
                : null,
            'usage' => [
                'prompt_tokens' => $response->usage->promptTokens ?? 0,
                'completion_tokens' => $response->usage->completionTokens ?? 0,
            ],
        ]);
    }

    /**
     * Past conversations for the signed-in user, newest activity first.
     */
    public function conversations(Request $request): JsonResponse
    {
        $page = DB::table('agent_conversations')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate(30);

        $ids = collect($page->items())->pluck('id')->all();

        $previews = [];
        $counts = [];

        if ($ids !== []) {
            $messages = DB::table('agent_conversation_messages')
                ->whereIn('conversation_id', $ids)
                ->orderBy('id')
                ->get(['conversation_id', 'content']);

            foreach ($messages->groupBy('conversation_id') as $id => $group) {
                $counts[$id] = $group->count();
                $previews[$id] = Str::limit((string) $group->last()->content, 120);
            }
        }

        return response()->json([
            'data' => collect($page->items())->map(fn ($conversation) => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'preview' => $previews[$conversation->id] ?? '',
                'messages_count' => $counts[$conversation->id] ?? 0,
                'created_at' => $conversation->created_at,
                'updated_at' => $conversation->updated_at,
            ])->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * One conversation with its messages, oldest first.
     */
    public function show(Request $request, string $conversation): JsonResponse
    {
        $row = $this->findOwnedConversation($request, $conversation);

        $messages = DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversation)
            ->orderBy('id')
            ->get(['id', 'role', 'content', 'attachments', 'created_at'])
            ->map(fn ($message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'image_url' => $this->attachmentImageUrl($message->attachments),
                'created_at' => $message->created_at,
            ]);

        return response()->json([
            'id' => $row->id,
            'title' => $row->title,
            'messages' => $messages,
            'updated_at' => $row->updated_at,
        ]);
    }

    /**
     * Remove a conversation and everything in it.
     */
    public function destroy(Request $request, string $conversation): JsonResponse
    {
        $row = $this->findOwnedConversation($request, $conversation);

        DB::transaction(function () use ($row, $conversation): void {
            $this->deleteAttachedImages($conversation);

            DB::table('agent_conversation_messages')
                ->where('conversation_id', $conversation)
                ->delete();

            DB::table('agent_conversations')->where('id', $row->id)->delete();
        });

        return response()->json(['message' => 'Conversation deleted.']);
    }

    /**
     * @return object{id:string,title:string,updated_at:string}
     */
    private function findOwnedConversation(Request $request, string $conversation): object
    {
        $row = DB::table('agent_conversations')
            ->where('id', $conversation)
            ->where('user_id', $request->user()->id)
            ->first(['id', 'title', 'updated_at']);

        abort_if($row === null, 404, 'Conversation not found.');

        return $row;
    }

    /**
     * Find the image attached to a stored message, if any.
     *
     * The attachments column holds whatever laravel/ai serialised, so the path
     * is located by looking for a string inside the coach-images folder rather
     * than by assuming a shape.
     */
    private function attachmentImageUrl(?string $attachments): ?string
    {
        if ($attachments === null || $attachments === '') {
            return null;
        }

        $decoded = json_decode($attachments, true);

        if (! is_array($decoded)) {
            return null;
        }

        foreach (Arr::flatten($decoded) as $value) {
            if (is_string($value) && str_starts_with($value, 'coach-images/')) {
                return url('/api/v1/media/coach-images/'.rawurlencode(basename($value)));
            }
        }

        return null;
    }

    /**
     * Delete the images attached to a conversation before its rows go.
     */
    private function deleteAttachedImages(string $conversation): void
    {
        $attachments = DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversation)
            ->pluck('attachments');

        foreach ($attachments as $raw) {
            $decoded = json_decode((string) $raw, true);

            if (! is_array($decoded)) {
                continue;
            }

            foreach (Arr::flatten($decoded) as $value) {
                if (is_string($value) && str_starts_with($value, 'coach-images/')) {
                    Storage::disk('public')->delete($value);
                }
            }
        }
    }
}
