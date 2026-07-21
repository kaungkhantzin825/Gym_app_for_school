<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('current_weight', 5, 2)->nullable();
            $table->decimal('target_weight', 5, 2)->nullable();
            $table->string('goal')->nullable();
            $table->string('activity_level')->nullable();
            $table->decimal('daily_calories_target', 8, 2)->nullable();
            $table->decimal('daily_protein_target', 8, 2)->nullable();
            $table->decimal('daily_carbs_target', 8, 2)->nullable();
            $table->decimal('daily_fat_target', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
