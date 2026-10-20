<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reps for a whole session had no column: the app wrote them into `notes` as
 * "Reps: 42 (tracked via live pose detection)" and read them back with a
 * regex. That made the number impossible to sort, sum or report on, and it
 * disappeared the moment a member edited the notes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->integer('total_reps')->nullable()->after('total_volume');
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropColumn('total_reps');
        });
    }
};
