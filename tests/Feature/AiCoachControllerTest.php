<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Coach history lives in `agent_conversations` / `agent_conversation_messages`,
 * written by laravel/ai. These tests cover the parts that do not need a live
 * model call: listing, reopening, deleting, and the ownership checks that stop
 * one account reading another account's chats.
 */
class AiCoachControllerTest extends TestCase
{
    use RefreshDatabase;

    private function conversation(User $user, string $title, ?string $updatedAt = null): string
    {
        $id = (string) Str::uuid();

        DB::table('agent_conversations')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'title' => $title,
            'created_at' => now(),
            'updated_at' => $updatedAt ?? now(),
        ]);

        return $id;
    }

    private int $messageSequence = 0;

    private function message(string $conversationId, User $user, string $role, string $content, array $attachments = []): void
    {
        // Messages are ordered by id, and laravel/ai writes uuid7 ids, so the
        // ids here are sequential: two uuid7 values created in the same
        // millisecond can sort the "wrong" way.
        DB::table('agent_conversation_messages')->insert([
            'id' => sprintf('0192f0c4-0000-7000-8000-%012d', ++$this->messageSequence),
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'agent' => 'App\\Ai\\Agents\\FitnessCoach',
            'role' => $role,
            'content' => $content,
            'attachments' => json_encode($attachments),
            'tool_calls' => '[]',
            'tool_results' => '[]',
            'usage' => '[]',
            'meta' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_it_lists_only_the_users_own_conversations_newest_first(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $old = $this->conversation($user, 'Older chat', now()->subDay()->toDateTimeString());
        $new = $this->conversation($user, 'Newer chat', now()->toDateTimeString());
        $this->conversation($other, 'Not yours');

        $this->message($old, $user, 'user', 'How do I bench more?');

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/ai/coach/conversations');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $new)
            ->assertJsonPath('data.1.id', $old)
            ->assertJsonCount(2, 'data');

        $this->assertSame('How do I bench more?', $response->json('data.1.preview'));
        $this->assertSame(1, $response->json('data.1.messages_count'));
    }

    public function test_it_reopens_a_conversation_with_its_messages_in_order(): void
    {
        $user = User::factory()->create();
        $id = $this->conversation($user, 'Leg day');

        $this->message($id, $user, 'user', 'My squat stalled.');
        $this->message($id, $user, 'assistant', 'Drop to 3x5 at 80%.');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/ai/coach/conversations/{$id}");

        $response->assertOk()
            ->assertJsonPath('title', 'Leg day')
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.0.role', 'user')
            ->assertJsonPath('messages.0.content', 'My squat stalled.')
            ->assertJsonPath('messages.1.role', 'assistant');
    }

    public function test_an_attached_image_is_returned_as_a_media_url(): void
    {
        $user = User::factory()->create();
        $id = $this->conversation($user, 'Form check');

        $this->message($id, $user, 'user', 'How is my setup?', [
            ['type' => 'image', 'path' => 'coach-images/abc123.jpg'],
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/ai/coach/conversations/{$id}")
            ->assertOk()
            ->assertJsonPath(
                'messages.0.image_url',
                url('/api/v1/media/coach-images/abc123.jpg'),
            );
    }

    public function test_another_users_conversation_is_not_readable(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $id = $this->conversation($owner, 'Private');

        $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/ai/coach/conversations/{$id}")
            ->assertNotFound();
    }

    public function test_deleting_removes_the_conversation_and_its_messages(): void
    {
        $user = User::factory()->create();
        $id = $this->conversation($user, 'Delete me');
        $this->message($id, $user, 'user', 'hello');
        $this->message($id, $user, 'assistant', 'hi');

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/ai/coach/conversations/{$id}")
            ->assertOk();

        $this->assertSame(0, DB::table('agent_conversations')->where('id', $id)->count());
        $this->assertSame(0, DB::table('agent_conversation_messages')->where('conversation_id', $id)->count());
    }

    public function test_another_users_conversation_cannot_be_deleted(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $id = $this->conversation($owner, 'Private');

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/ai/coach/conversations/{$id}")
            ->assertNotFound();

        $this->assertSame(1, DB::table('agent_conversations')->where('id', $id)->count());
    }

    public function test_it_will_not_continue_someone_elses_conversation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $id = $this->conversation($owner, 'Private');

        // Rejected before the model is ever called.
        $this->actingAs($intruder, 'sanctum')
            ->postJson('/api/v1/ai/coach/chat', [
                'message' => 'Continue this',
                'conversation_id' => $id,
            ])
            ->assertNotFound();
    }

    public function test_a_message_is_required_when_no_image_is_sent(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/ai/coach/chat', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('message');
    }

    public function test_the_coach_endpoints_require_a_token(): void
    {
        $this->getJson('/api/v1/ai/coach/conversations')->assertUnauthorized();
        $this->postJson('/api/v1/ai/coach/chat', ['message' => 'hi'])->assertUnauthorized();
    }
}
