<?php

namespace Tests\Feature;

use App\Models\TutorialVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorialVideoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutorial_videos_are_public(): void
    {
        TutorialVideo::factory()->count(3)->create();

        $this->getJson('/api/v1/tutorial-videos')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_gender_filter_includes_unisex_videos(): void
    {
        TutorialVideo::factory()->create(['gender_target' => 'boy']);
        TutorialVideo::factory()->create(['gender_target' => 'girl']);
        TutorialVideo::factory()->create(['gender_target' => 'both']);

        $response = $this->getJson('/api/v1/tutorial-videos?gender=boy');

        $response->assertOk()->assertJsonCount(2, 'data');

        $targets = collect($response->json('data'))->pluck('gender_target')->sort()->values();
        $this->assertSame(['both', 'boy'], $targets->all());
    }

    public function test_muscle_group_filter(): void
    {
        TutorialVideo::factory()->create(['muscle_group' => 'chest']);
        TutorialVideo::factory()->count(2)->create(['muscle_group' => 'legs']);

        $this->getJson('/api/v1/tutorial-videos?muscle_group=chest')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.muscle_group', 'chest');
    }

    public function test_gender_and_muscle_group_filters_combine(): void
    {
        TutorialVideo::factory()->create([
            'gender_target' => 'boy',
            'muscle_group' => 'chest',
        ]);
        TutorialVideo::factory()->create([
            'gender_target' => 'both',
            'muscle_group' => 'chest',
        ]);
        // Same gender, different muscle group: must be excluded.
        TutorialVideo::factory()->create([
            'gender_target' => 'boy',
            'muscle_group' => 'legs',
        ]);

        $response = $this->getJson('/api/v1/tutorial-videos?gender=boy&muscle_group=chest');

        $response->assertOk()->assertJsonCount(2, 'data');

        $groups = collect($response->json('data'))->pluck('muscle_group')->unique()->values();
        $this->assertSame(['chest'], $groups->all());
    }

    public function test_show_returns_a_single_video(): void
    {
        $video = TutorialVideo::factory()->create();

        $this->getJson("/api/v1/tutorial-videos/{$video->id}")
            ->assertOk()
            ->assertJsonPath('id', $video->id)
            ->assertJsonPath('title', $video->title);
    }

    public function test_show_returns_404_for_an_unknown_video(): void
    {
        $this->getJson('/api/v1/tutorial-videos/999')->assertNotFound();
    }
}
