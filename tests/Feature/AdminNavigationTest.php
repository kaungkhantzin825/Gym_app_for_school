<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Exercise;
use App\Models\Meal;
use App\Models\SupportMessage;
use App\Models\TutorialVideo;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every item in the admin sidebar must actually open.
 *
 * The panel is navigated by hand, so a single resource page that throws would
 * read as "the menu is not working" rather than as a specific failure. This
 * walks the sidebar instead of trusting that the routes exist.
 */
class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function sidebarProvider(): array
    {
        return [
            'dashboard' => ['/admin'],
            'users' => ['/admin/users'],
            'member report' => ['/admin/user-report-page'],
            'workouts' => ['/admin/workouts'],
            'exercises' => ['/admin/exercises'],
            'meals' => ['/admin/meals'],
            'tutorial videos' => ['/admin/tutorial-videos'],
            'support messages' => ['/admin/support-messages'],
            'app settings' => ['/admin/app-settings'],
        ];
    }

    #[DataProvider('sidebarProvider')]
    public function test_every_sidebar_page_opens(string $uri): void
    {
        $this->actingAs($this->admin)
            ->get($uri)
            ->assertOk();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function createPageProvider(): array
    {
        return [
            'users' => ['/admin/users/create'],
            'workouts' => ['/admin/workouts/create'],
            'exercises' => ['/admin/exercises/create'],
            'meals' => ['/admin/meals/create'],
            'tutorial videos' => ['/admin/tutorial-videos/create'],
            'support messages' => ['/admin/support-messages/create'],
            'app settings' => ['/admin/app-settings/create'],
        ];
    }

    #[DataProvider('createPageProvider')]
    public function test_every_create_page_opens(string $uri): void
    {
        $this->actingAs($this->admin)
            ->get($uri)
            ->assertOk();
    }

    /**
     * Edit pages are the ones most likely to break: they resolve a record and
     * then render every field from the real row.
     *
     * @return array<string, array{0: string}>
     */
    public static function editPageProvider(): array
    {
        return [
            'user' => ['users'],
            'workout' => ['workouts'],
            'exercise' => ['exercises'],
            'meal' => ['meals'],
            'tutorial video' => ['tutorial-videos'],
            'support message' => ['support-messages'],
            'app setting' => ['app-settings'],
        ];
    }

    #[DataProvider('editPageProvider')]
    public function test_every_edit_page_opens(string $resource): void
    {
        $record = $this->makeRecord($resource);

        $this->actingAs($this->admin)
            ->get("/admin/{$resource}/{$record->getKey()}/edit")
            ->assertOk();
    }

    private function makeRecord(string $resource): object
    {
        // Records belong to somebody other than the admin on purpose: that is
        // the case the owner-only policies used to 403.
        $owner = User::factory()->create(['role' => 'user']);

        return match ($resource) {
            'users' => $owner,
            'workouts' => Workout::factory()->create(['user_id' => $owner->id]),
            'exercises' => Exercise::factory()->create(['user_id' => $owner->id]),
            'meals' => Meal::factory()->create(['user_id' => $owner->id]),
            'tutorial-videos' => TutorialVideo::factory()->create(),
            'support-messages' => SupportMessage::factory()->create(['user_id' => $owner->id]),
            'app-settings' => AppSetting::create(['key' => 'support_email', 'value' => 'help@gym.app']),
        };
    }

    public function test_a_member_still_cannot_reach_another_members_workout(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);

        $workout = Workout::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('view', $workout));
        $this->assertFalse($stranger->can('view', $workout));
        $this->assertTrue($this->admin->can('view', $workout));
    }

    public function test_the_shortcut_redirects_still_land_on_a_page(): void
    {
        foreach (['/user', '/users', '/report', '/reports'] as $shortcut) {
            $response = $this->actingAs($this->admin)->get($shortcut);

            $response->assertRedirect();

            $this->actingAs($this->admin)
                ->get($response->headers->get('Location'))
                ->assertOk();
        }
    }
}
