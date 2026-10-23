<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use App\Services\AiKeyRotatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The AI settings endpoints read and write the live provider keys, and
 * `index` returns them unmasked when `?include_raw=1` is passed. They were
 * once declared in the public block of routes/api.php, which handed the
 * Gemini and Mistral keys to anybody who asked for that URL.
 */
class AdminAiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_read_the_provider_keys(): void
    {
        $this->getJson('/api/v1/admin/ai-settings')->assertUnauthorized();
    }

    public function test_a_guest_cannot_dump_the_raw_keys(): void
    {
        AppSetting::setValue('mistral_api_keys', 'sk-live-secret');

        $this->getJson('/api/v1/admin/ai-settings?include_raw=1')
            ->assertUnauthorized()
            ->assertDontSee('sk-live-secret');
    }

    public function test_a_guest_cannot_rewrite_the_settings(): void
    {
        $this->putJson('/api/v1/admin/ai-settings', [
            'primary_provider' => 'gemini',
        ])->assertUnauthorized();

        $this->postJson('/api/v1/admin/ai-settings/test')->assertUnauthorized();
    }

    public function test_a_regular_member_is_forbidden(): void
    {
        // Bootstrap mode admits the first member, so an admin has to exist
        // before the gate starts rejecting non-admins.
        User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($member, 'sanctum')
            ->getJson('/api/v1/admin/ai-settings')
            ->assertForbidden();
    }

    public function test_an_admin_can_read_the_settings(): void
    {
        AppSetting::setValue('mistral_api_keys', 'sk-live-secret');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/ai-settings')
            ->assertOk()
            // Keys also come from .env, so the count is not fixed; what
            // matters is that the masked view never leaks the secret.
            ->assertJsonPath('primary_provider', AiKeyRotatorService::getPrimaryProvider())
            ->assertDontSee('sk-live-secret');
    }

    public function test_an_admin_can_dump_the_raw_keys_when_asked(): void
    {
        AppSetting::setValue('mistral_api_keys', 'sk-live-secret');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/ai-settings?include_raw=1')
            ->assertOk()
            ->assertJsonPath('mistral_keys_raw', 'sk-live-secret');
    }

    public function test_an_admin_can_change_the_primary_provider(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/admin/ai-settings', ['primary_provider' => 'gemini'])
            ->assertOk()
            ->assertJsonPath('primary_provider', 'gemini');

        $this->assertSame('gemini', AppSetting::getValue('ai_primary_provider'));
    }
}
