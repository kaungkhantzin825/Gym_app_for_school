<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The generic resource controller: one code path, seven screens.
 *
 * These cover the behaviour that is shared by all of them — creating,
 * updating, deleting, validating, searching and paging — plus the two rules
 * that differ per resource: ownership stamping and unique keys.
 */
class AdminResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_a_member_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Daw Mya',
                'email' => 'daw@example.com',
                'phone' => '+959123456',
                'role' => 'user',
                'password' => 'secret-password',
            ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'name' => 'Daw Mya',
            'email' => 'daw@example.com',
            'role' => 'user',
        ]);
    }

    public function test_a_created_password_is_hashed(): void
    {
        $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'Hashed',
            'email' => 'hashed@example.com',
            'role' => 'user',
            'password' => 'secret-password',
        ]);

        $user = User::where('email', 'hashed@example.com')->firstOrFail();

        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_a_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Copy',
                'email' => 'taken@example.com',
                'role' => 'user',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_an_invalid_email_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/users', ['name' => 'Nobody', 'email' => 'not-an-email', 'role' => 'user'])
            ->assertSessionHasErrors('email');
    }

    public function test_a_member_can_be_updated(): void
    {
        $member = User::factory()->create(['name' => 'Old Name', 'role' => 'user']);

        $this->actingAs($this->admin)
            ->put("/admin/users/{$member->id}", [
                'name' => 'New Name',
                'email' => $member->email,
                'role' => 'user',
            ])
            ->assertRedirect('/admin/users');

        $this->assertSame('New Name', $member->fresh()->name);
    }

    public function test_keeping_the_email_unchanged_is_not_a_duplicate(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($this->admin)
            ->put("/admin/users/{$member->id}", [
                'name' => $member->name,
                'email' => $member->email,
                'role' => 'user',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_a_blank_password_on_edit_keeps_the_existing_one(): void
    {
        $member = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('original-password'),
        ]);

        $before = $member->password;

        $this->actingAs($this->admin)->put("/admin/users/{$member->id}", [
            'name' => $member->name,
            'email' => $member->email,
            'role' => 'user',
            'password' => '',
        ]);

        $this->assertSame($before, $member->fresh()->password);
    }

    public function test_a_member_can_be_deleted(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($this->admin)
            ->delete("/admin/users/{$member->id}")
            ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_an_exercise_created_in_the_panel_belongs_to_the_admin(): void
    {
        $this->actingAs($this->admin)->post('/admin/exercises', [
            'exercise_name' => 'Front Squat',
            'exercise_type' => 'strength',
            'sets' => 4,
            'reps' => 8,
            'weight' => 60,
            'exercise_time' => '2026-03-15T09:00',
        ])->assertRedirect('/admin/exercises');

        $exercise = Exercise::where('exercise_name', 'Front Squat')->firstOrFail();

        $this->assertSame($this->admin->id, $exercise->user_id);
    }

    public function test_an_unknown_select_value_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/exercises', [
                'exercise_name' => 'Made Up',
                'exercise_type' => 'teleportation',
                'exercise_time' => '2026-03-15T09:00',
            ])
            ->assertSessionHasErrors('exercise_type');
    }

    public function test_a_setting_key_must_be_unique(): void
    {
        AppSetting::create(['key' => 'support_email', 'value' => 'a@gym.app']);

        $this->actingAs($this->admin)
            ->post('/admin/app-settings', ['key' => 'support_email', 'value' => 'b@gym.app'])
            ->assertSessionHasErrors('key');
    }

    public function test_the_search_filters_the_list(): void
    {
        User::factory()->create(['name' => 'Alpha Member', 'role' => 'user']);
        User::factory()->create(['name' => 'Beta Member', 'role' => 'user']);

        $this->actingAs($this->admin)
            ->get('/admin/users?search=Alpha')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Resource/Index')
                    ->has('rows', 1)
                    ->where('rows.0.cells.name.display', 'Alpha Member')
                    ->where('filters.search', 'Alpha')
            );
    }

    public function test_the_list_pages_at_fifteen_rows(): void
    {
        User::factory()->count(20)->create(['role' => 'user']);

        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('rows', 15)
                    ->where('pagination.total', 21)
                    ->where('pagination.pages', 2)
            );
    }

    public function test_a_badge_column_carries_its_tone(): void
    {
        User::factory()->create(['role' => 'admin', 'name' => 'The Admin']);

        $this->actingAs($this->admin)
            ->get('/admin/users?search=The+Admin')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('rows.0.cells.role.display', 'admin')
                    ->where('rows.0.cells.role.tone', 'lime')
            );
    }
}
