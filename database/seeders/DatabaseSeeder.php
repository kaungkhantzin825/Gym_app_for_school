<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\TutorialVideo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@gym.app',
        ]);

        // Test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Tutorial videos
        TutorialVideo::factory(10)->create();

        // Admin exercise catalog
        $this->call(AdminExerciseSeeder::class);

        // App settings
        AppSetting::setValue('about', 'GYM APP - Your AI-powered fitness companion.');
        AppSetting::setValue('privacy_policy', 'Your privacy is important to us.');
    }
}
