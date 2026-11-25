<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * More photos of a team member, shown in turn after the main photo (list of paths on the public disk).
     */
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }
};
