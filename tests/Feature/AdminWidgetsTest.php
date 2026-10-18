<?php

namespace Tests\Feature;

use App\Filament\Pages\UserReportPage;
use App\Filament\Widgets\RecentMembersWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TopExercisesWidget;
use App\Filament\Widgets\TrainingVolumeWidget;
use App\Models\Exercise;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Filament widgets are lazy-loaded Livewire components, so they never appear
 * in the dashboard HTML. Rendering each one directly is the only way to know
 * its queries actually run.
 */
class AdminWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    public function test_the_stats_overview_renders_with_no_data(): void
    {
        Livewire::test(StatsOverviewWidget::class)
            ->assertOk()
            ->assertSee('Members')
            ->assertSee('Active this week')
            ->assertSee('Volume lifted')
            ->assertSee('Average form')
            ->assertSee('0% of the member base')
            ->assertSee('No camera-scored sets yet');
    }

    public function test_the_stats_overview_reports_volume_and_form(): void
    {
        $this->logSession(reps: 10, weight: 60, formScore: 88);

        Livewire::test(StatsOverviewWidget::class)
            ->assertOk()
            ->assertSee('600 kg')
            ->assertSee('88%');
    }

    public function test_the_training_volume_chart_renders_fourteen_days(): void
    {
        $this->logSession(reps: 10, weight: 50);

        Livewire::test(TrainingVolumeWidget::class)
            ->assertOk()
            ->assertSee('Training volume, last 14 days');
    }

    public function test_the_top_exercises_table_renders(): void
    {
        $this->logSession(reps: 10, weight: 80);

        Livewire::test(TopExercisesWidget::class)
            ->assertOk()
            ->assertSee('Top exercises, last 30 days')
            ->assertSee('Bench press');
    }

    public function test_the_newest_members_table_renders(): void
    {
        User::factory()->create(['name' => 'Fresh Member', 'role' => 'user']);

        Livewire::test(RecentMembersWidget::class)
            ->assertOk()
            ->assertSee('Newest members')
            ->assertSee('Fresh Member');
    }

    public function test_the_member_report_page_renders(): void
    {
        $this->logSession(reps: 10, weight: 70, formScore: 80);

        Livewire::test(UserReportPage::class)
            ->assertOk()
            ->assertSee('Member report')
            ->assertSee('Most active members')
            ->assertSee('Avg form')
            ->assertSee('80%');
    }

    public function test_the_top_exercises_table_does_not_use_an_eloquent_query(): void
    {
        $this->logSession(reps: 10, weight: 80);

        $table = Livewire::test(TopExercisesWidget::class)->instance()->getTable();

        // Model-backed tables get `ORDER BY workout_sets.id` appended for a
        // stable sort, and MySQL ONLY_FULL_GROUP_BY rejects that column in a
        // grouped query (error 1055). records() keeps Filament out of the SQL.
        $this->assertFalse($table->hasQuery());
    }

    /**
     * One saved workout with a single set, through the real models.
     */
    private function logSession(int $reps, float $weight, ?int $formScore = null): void
    {
        $workout = Workout::create([
            'user_id' => $this->admin->id,
            'name' => 'Push day',
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'total_duration_minutes' => 60,
            'total_volume' => $reps * $weight,
        ]);

        $exercise = Exercise::create([
            'user_id' => $this->admin->id,
            'exercise_name' => 'Bench press',
            'exercise_type' => 'strength',
            'exercise_time' => now()->subHours(2),
        ]);

        WorkoutSet::create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
            'set_number' => 1,
            'reps' => $reps,
            'weight' => $weight,
            'form_score' => $formScore,
        ]);
    }
}
