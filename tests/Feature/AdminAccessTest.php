<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who gets into /admin, and how.
 *
 * The panel is Inertia + Vue now, so these tests assert on the HTTP contract
 * instead of on a panel object.
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_open_the_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('INTI Gym', escape: false);
    }

    public function test_a_regular_member_is_forbidden_once_an_admin_exists(): void
    {
        User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($member)->get('/admin')->assertForbidden();
    }

    public function test_bootstrap_mode_admits_members_until_an_admin_exists(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($member)->get('/admin')->assertOk();
    }

    public function test_an_allowlisted_email_can_recover_access(): void
    {
        User::factory()->create(['role' => 'admin']);
        config(['gym.admin_emails' => ['owner@example.com']]);

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'role' => 'user',
        ]);

        $this->actingAs($owner)->get('/admin')->assertOk();
    }

    public function test_the_allowlist_is_case_insensitive(): void
    {
        User::factory()->create(['role' => 'admin']);
        config(['gym.admin_emails' => ['Owner@Example.com']]);

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'role' => 'user',
        ]);

        $this->assertTrue($owner->canAccessAdmin());
    }

    public function test_a_guest_is_sent_to_the_login_screen(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_the_login_screen_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    /**
     * public/build is gitignored, so a deploy that pulls the code without
     * running `npm run build` has no manifest. That must produce a readable
     * instruction rather than a Vite stack trace.
     */
    public function test_a_missing_asset_build_explains_itself(): void
    {
        $emptyPublicPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'inti-public-'.uniqid();
        mkdir($emptyPublicPath, 0777, true);

        $originalPublicPath = public_path();
        $this->app->usePublicPath($emptyPublicPath);

        try {
            $this->get('/admin/login')
                ->assertOk()
                ->assertSee('The admin assets have not been built', escape: false)
                ->assertDontSee('/build/assets/', escape: false);
        } finally {
            $this->app->usePublicPath($originalPublicPath);
            @rmdir($emptyPublicPath);
        }
    }

    public function test_an_admin_can_sign_in(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('correct-horse'),
        ]);

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'correct-horse',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_member_cannot_sign_in_even_with_the_right_password(): void
    {
        User::factory()->create(['role' => 'admin']);

        $member = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('correct-horse'),
        ]);

        $this->post('/admin/login', [
            'email' => $member->email,
            'password' => 'correct-horse',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_admin_can_sign_out(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }

    public function test_the_promote_command_grants_admin_to_an_existing_account(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->artisan('admin:promote', ['email' => $user->email])
            ->expectsOutputToContain('Granted admin access')
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_the_promote_command_creates_a_missing_account(): void
    {
        $this->artisan('admin:promote', [
            'email' => 'new-admin@example.com',
            '--password' => 'secret-1234',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_the_promote_command_is_idempotent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->artisan('admin:promote', ['email' => $admin->email])
            ->expectsOutputToContain('already an admin')
            ->assertSuccessful();

        $this->assertSame(1, User::where('role', 'admin')->count());
    }
}
