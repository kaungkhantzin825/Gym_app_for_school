<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_open_the_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
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

        $this->assertTrue($owner->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_guests_are_sent_to_the_login_screen(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_the_dashboard_renders_for_an_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('INTI Gym');
    }

    public function test_the_panel_loads_the_compiled_theme(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('build/assets/theme-', escape: false);
    }

    /**
     * public/build is gitignored, so a deploy that pulls the code without
     * running `npm run build` used to 500 the whole panel with "Unable to
     * locate file in Vite manifest". The panel must fall back to Filament's
     * own published assets instead.
     */
    public function test_the_theme_is_skipped_when_the_vite_manifest_is_missing(): void
    {
        $emptyPublicPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'inti-public-'.uniqid();
        mkdir($emptyPublicPath, 0777, true);

        $originalPublicPath = public_path();
        $this->app->usePublicPath($emptyPublicPath);

        try {
            $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

            $this->assertNull($panel->getViteTheme());
        } finally {
            $this->app->usePublicPath($originalPublicPath);
            @rmdir($emptyPublicPath);
        }
    }

    public function test_the_theme_is_registered_when_it_has_been_built(): void
    {
        if (! is_file(public_path('build/manifest.json'))) {
            $this->markTestSkipped('Vite assets have not been built.');
        }

        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertSame(
            'resources/css/filament/admin/theme.css',
            $panel->getViteTheme()
        );
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
