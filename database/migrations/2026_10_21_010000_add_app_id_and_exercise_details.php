<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_profiles') && !Schema::hasColumn('user_profiles', 'app_id')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->string('app_id', 64)->nullable()->index()->after('user_id');
            });
        }

        if (Schema::hasTable('tutorial_videos')) {
            Schema::table('tutorial_videos', function (Blueprint $table) {
                if (!Schema::hasColumn('tutorial_videos', 'focus_areas')) {
                    $table->json('focus_areas')->nullable()->after('muscle_group');
                }
                if (!Schema::hasColumn('tutorial_videos', 'equipment')) {
                    $table->string('equipment', 64)->nullable()->after('focus_areas');
                }
                if (!Schema::hasColumn('tutorial_videos', 'how_to_do')) {
                    $table->text('how_to_do')->nullable()->after('equipment');
                }
            });
        }

        // Create guest_profiles table for guests who haven't registered an account yet
        if (!Schema::hasTable('guest_profiles')) {
            Schema::create('guest_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('app_id', 64)->unique()->index();
                $table->string('gender', 16)->default('male');
                $table->integer('age')->nullable();
                $table->decimal('height', 5, 2)->nullable();
                $table->decimal('current_weight', 5, 2)->nullable();
                $table->string('goal', 64)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_profiles') && Schema::hasColumn('user_profiles', 'app_id')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropColumn('app_id');
            });
        }

        if (Schema::hasTable('tutorial_videos')) {
            Schema::table('tutorial_videos', function (Blueprint $table) {
                $table->dropColumn(['focus_areas', 'equipment', 'how_to_do']);
            });
        }

        Schema::dropIfExists('guest_profiles');
    }
};
