<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->constrained()->cascadeOnDelete();
            $table->string('food_name');
            $table->string('fatsecret_food_id')->nullable()->index();
            $table->string('brand_name')->nullable();
            $table->decimal('serving_size', 8, 2)->nullable();
            $table->string('serving_unit')->nullable();
            $table->decimal('calories', 8, 2)->default(0);
            $table->decimal('protein', 8, 2)->default(0);
            $table->decimal('carbs', 8, 2)->default(0);
            $table->decimal('fat', 8, 2)->default(0);
            $table->decimal('fiber', 8, 2)->nullable();
            $table->decimal('sugar', 8, 2)->nullable();
            $table->decimal('sodium', 8, 2)->nullable();
            $table->timestamps();

            $table->index('meal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_logs');
    }
};
