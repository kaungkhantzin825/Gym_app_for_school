<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camera_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('app_id', 64)->nullable()->index();

            // Generated on the phone. A retry after a flaky upload must not
            // create a second copy of the same session.
            $table->string('client_id', 64)->nullable()->unique();

            $table->string('exercise_type', 32);
            $table->string('exercise_name', 100);
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('sets_count')->default(0);
            $table->unsignedInteger('total_reps')->default(0);
            $table->unsignedTinyInteger('avg_form_score')->nullable();
            $table->json('sets')->nullable();
            $table->string('source', 32)->default('pose_tracker');
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_sessions');
    }
};
