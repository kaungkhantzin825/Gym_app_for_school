<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('exercise_name');
            $table->string('exercise_type')->index();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('calories_burned', 8, 2)->nullable();
            $table->integer('sets')->nullable();
            $table->integer('reps')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->timestamp('exercise_time');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'exercise_time']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
