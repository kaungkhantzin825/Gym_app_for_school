<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_the_profile_and_supports_name_changes(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        UserProfile::factory()->for($user)->create([
            'activity_level' => 'light',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/profile', [
            'name' => 'New Name',
            'goal' => 'build_muscle',
            'activity_level' => 'active',
            'daily_protein_target' => 180,
        ]);

        $response->assertOk()->assertJson([
            'name' => 'New Name',
            'goal' => 'build_muscle',
            'activity_level' => 'active',
            'daily_protein_target' => '180.00',
        ]);

        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame('build_muscle', $user->profile->fresh()->goal);
    }

    public function test_it_uploads_a_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/profile/photo', [
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated()->assertJson([
            'message' => 'Profile photo uploaded successfully',
        ]);

        $this->assertNotNull($response->json('profile_photo'));
        Storage::disk('public')->assertExists($response->json('profile_photo'));
        $this->assertSame($response->json('profile_photo'), $user->fresh()->profile_photo);
    }

    public function test_it_replaces_the_previous_profile_photo_when_uploading_a_new_one(): void
    {
        Storage::fake('public');

        $oldPhotoPath = UploadedFile::fake()->image('old-avatar.jpg')->store('profile-photos', 'public');

        $user = User::factory()->create([
            'profile_photo' => $oldPhotoPath,
        ]);

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/profile/photo', [
            'photo' => UploadedFile::fake()->image('new-avatar.jpg'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated();

        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($response->json('profile_photo'));
    }

    public function test_show_includes_name_and_profile_photo_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Profile Name',
            'profile_photo' => 'profile-photos/example.jpg',
        ]);

        UserProfile::factory()->for($user)->create([
            'goal' => 'maintain',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/profile');

        $response->assertOk()->assertJson([
            'name' => 'Profile Name',
            'profile_photo' => 'profile-photos/example.jpg',
            'profile_photo_url' => url('/storage/profile-photos/example.jpg'),
            'goal' => 'maintain',
        ]);
    }
}
