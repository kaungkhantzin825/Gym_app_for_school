<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\TutorialVideo;
use App\Models\User;
use App\Models\UserProfile;
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
        // Admin user — use updateOrCreate so re-seeding is idempotent
        $admin = User::updateOrCreate(
            ['email' => 'admin@gym.app'],
            [
                'name'              => 'Admin',
                'phone'             => '+13479643026',
                'password'          => bcrypt('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'gender'         => 'male',
                'age'            => 30,
                'height'         => 180,
                'current_weight' => 80,
                'target_weight'  => 80,
                'goal'           => 'build_muscle',
                'activity_level' => 'very_active',
            ]
        );

        // Male user
        $male = User::updateOrCreate(
            ['email' => 'male@gym.app'],
            [
                'name'              => 'Alexander Vance',
                'password'          => bcrypt('password'),
                'role'              => 'user',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $male->id],
            [
                'gender'         => 'male',
                'age'            => 26,
                'height'         => 178,
                'current_weight' => 78,
                'target_weight'  => 82,
                'goal'           => 'build_muscle',
                'activity_level' => 'moderate',
            ]
        );

        // Female user
        $female = User::updateOrCreate(
            ['email' => 'female@gym.app'],
            [
                'name'              => 'Elena Rostova',
                'password'          => bcrypt('password'),
                'role'              => 'user',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $female->id],
            [
                'gender'         => 'female',
                'age'            => 24,
                'height'         => 165,
                'current_weight' => 56,
                'target_weight'  => 54,
                'goal'           => 'tone_and_lean',
                'activity_level' => 'active',
            ]
        );

        // Test user
        $test = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'              => 'Test User',
                'password'          => bcrypt('password'),
                'role'              => 'user',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $test->id],
            [
                'gender'         => 'other',
                'age'            => 25,
                'height'         => 172,
                'current_weight' => 68,
                'target_weight'  => 70,
                'goal'           => 'general_fitness',
                'activity_level' => 'moderate',
            ]
        );

        // Tutorial videos
        $this->call(TutorialVideoSeeder::class);

        // Admin exercise catalog
        $this->call(AdminExerciseSeeder::class);

        // App settings
        AppSetting::setValue('about', 'INTI Gym APP - Your AI-powered fitness companion.');
        AppSetting::setValue('privacy_policy', 'Your privacy is important to us.');
    }
}
