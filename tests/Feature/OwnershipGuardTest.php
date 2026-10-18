<?php

namespace Tests\Feature;

use App\Models\PhotoGallery;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\WeightLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnershipGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_logs_are_scoped_to_the_user(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        WeightLog::factory()->for($user)->count(2)->create();
        WeightLog::factory()->for($stranger)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/weight-logs')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_weight_log_weight_must_be_physically_plausible(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/weight-logs', [
                'weight' => 10,
                'logged_at' => now()->toIso8601String(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('weight');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/weight-logs', [
                'weight' => 72.5,
                'logged_at' => now()->toIso8601String(),
            ])
            ->assertCreated()
            ->assertJsonPath('user_id', $user->id);
    }

    public function test_a_user_cannot_delete_another_users_weight_log(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $log = WeightLog::factory()->for($owner)->create();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/weight-logs/{$log->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('weight_logs', ['id' => $log->id]);
    }

    public function test_support_messages_are_private_to_the_sender(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        SupportMessage::factory()->for($user)->create();
        SupportMessage::factory()->for($stranger)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/support-messages')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/support-messages', [
                'subject' => 'Cannot log meals',
                'message' => 'The meal screen stays empty.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'pending');
    }

    public function test_support_message_requires_subject_and_body(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/support-messages', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['subject', 'message']);
    }

    public function test_gallery_requires_an_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/v1/photo-galleries', ['caption' => 'No file'], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photo');
    }

    public function test_gallery_upload_stores_the_file_for_the_owner(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/v1/photo-galleries', [
                'photo' => UploadedFile::fake()->image('progress.jpg'),
                'caption' => 'Week 4',
            ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('caption', 'Week 4')
            ->assertJsonPath('user_id', $user->id);

        $path = $response->json('photo_url');
        $this->assertStringStartsWith('gallery/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_a_user_cannot_delete_another_users_photo(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $photo = PhotoGallery::factory()->for($owner)->create();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/photo-galleries/{$photo->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('photo_galleries', ['id' => $photo->id]);
    }
}
