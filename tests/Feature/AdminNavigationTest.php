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
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every item in the admin sidebar must actually open.
 *
 * The panel is navigated by hand, so a single screen that throws reads as
 * "the menu is not working" rather than as a specific failure. This walks the
 * sidebar instead of trusting that the routes exist.
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
            'calendar' => ['/admin/calendar'],
            'member report' => ['/admin/report'],
            'users' => ['/admin/users'],
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
        $this->actingAs($this->admin)->get($uri)->assertOk();
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
        $this->actingAs($this->admin)->get($uri)->assertOk();
    }

    /**
     * Edit pages are the ones most likely to break: they resolve a record and
     * then hydrate every field from the real row.
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

    public function test_the_dashboard_carries_the_stats_and_series(): void
    {
        $this->actingAs($this->admin)->get('/admin')->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('headline', 4)
                ->has('series.labels', 14)
                ->has('series.workouts', 14)
                ->has('series.meals', 14)
                ->has('topExercises')
                ->has('recentWorkouts')
                ->has('recentMembers')
        );
    }

    public function test_the_report_page_lists_the_most_active_members(): void
    {
        $busy = User::factory()->create(['role' => 'user', 'name' => 'Busy Bee']);
        Workout::factory()->count(3)->create(['user_id' => $busy->id]);

        $this->actingAs($this->admin)->get('/admin/report')->assertInertia(
            fn (Assert $page) => $page
                ->component('Report')
                ->has('headline', 4)
                ->where('topMembers.0.name', 'Busy Bee')
                ->where('topMembers.0.workouts', 3)
        );
    }

    public function test_the_calendar_grid_covers_whole_weeks(): void
    {
        // March 2026 runs from a Sunday to a Tuesday, so it needs six rows.
        $this->actingAs($this->admin)
            ->get('/admin/calendar?month=2026-03&day=2026-03-15')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Calendar')
                    ->where('month', '2026-03')
                    ->where('label', 'March 2026')
                    ->where('selected', '2026-03-15')
                    ->has('weeks', 6)
                    ->has('weeks.0', 7)
                    ->has('records.workouts')
                    ->has('records.meals')
                    ->has('summary')
            );
    }

    public function test_the_calendar_starts_each_week_on_monday(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/calendar?month=2026-03')
            ->assertInertia(
                fn (Assert $page) => $page->where('weeks.0.0.date', '2026-02-23')
            );
    }

    public function test_the_calendar_counts_records_on_the_day_they_happened(): void
    {
        $owner = User::factory()->create(['role' => 'user']);

        $this->stamp(
            Workout::factory()->create(['user_id' => $owner->id]),
            Carbon::parse('2026-03-15 09:30'),
        );

        $this->stamp(
            Meal::factory()->create(['user_id' => $owner->id]),
            Carbon::parse('2026-03-15 12:30'),
        );

        $this->actingAs($this->admin)
            ->get('/admin/calendar?month=2026-03&day=2026-03-15')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('summary.workouts', 1)
                    ->where('summary.meals', 1)
                    ->has('records.workouts', 1)
                    ->has('records.meals', 1)
                    ->where('records.workouts.0.time', '09:30')
            );
    }

    public function test_a_day_with_nothing_recorded_is_empty(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/calendar?month=2026-03&day=2026-03-02')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('records.workouts', 0)
                    ->has('records.meals', 0)
            );
    }

    public function test_opening_a_form_from_the_calendar_prefills_the_date(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/meals/create?date=2026-03-15')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Resource/Form')
                    ->where('fields', fn ($fields) => collect($fields)
                        ->firstWhere('key', 'meal_time')['value'] === '2026-03-15T09:00')
            );
    }

    public function test_a_malformed_calendar_date_is_ignored(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/meals/create?date=not-a-date')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('fields', fn ($fields) => collect($fields)
                        ->firstWhere('key', 'meal_time')['value'] === '')
            );
    }

    public function test_an_unknown_resource_is_a_404(): void
    {
        $this->actingAs($this->admin)->get('/admin/not-a-resource')->assertNotFound();
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

    private function stamp(object $model, Carbon $when): void
    {
        $model->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
    }
}
