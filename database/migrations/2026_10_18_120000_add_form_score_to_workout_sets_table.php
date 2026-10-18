<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_sets', function (Blueprint $table) {
            // 0-100 form score sampled from the live pose tracker. Null for
            // sets logged manually or from an AI plan, where no camera ran.
            $table->unsignedTinyInteger('form_score')
                ->nullable()
                ->after('rest_time_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('workout_sets', function (Blueprint $table) {
            $table->dropColumn('form_score');
        });
    }
};
