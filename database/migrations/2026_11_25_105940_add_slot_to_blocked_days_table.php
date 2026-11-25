<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A block can cover the whole day or only one slot (morning / afternoon).
     * Existing rows block the whole day; one row per date and part of the day.
     */
    public function up(): void
    {
        Schema::table('blocked_days', function (Blueprint $table) {
            $table->string('slot', 16)->default('day')->after('date');
            $table->dropUnique(['date']);
            $table->unique(['date', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::table('blocked_days', function (Blueprint $table) {
            $table->dropUnique(['date', 'slot']);
        });

        // Only one row per date fits the original schema: keep the whole-day blocks.
        DB::table('blocked_days')->where('slot', '!=', 'day')->delete();

        Schema::table('blocked_days', function (Blueprint $table) {
            $table->dropColumn('slot');
            $table->unique('date');
        });
    }
};
