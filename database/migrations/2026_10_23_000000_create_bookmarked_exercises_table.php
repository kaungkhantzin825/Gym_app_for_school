<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookmarked_exercises')) {
            Schema::create('bookmarked_exercises', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('app_id', 64)->nullable()->index();
                $table->foreignId('tutorial_video_id')->nullable()->constrained('tutorial_videos')->nullOnDelete();
                $table->string('exercise_title');
                $table->string('muscle_group')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'exercise_title']);
                $table->index(['app_id', 'exercise_title']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarked_exercises');
    }
};
