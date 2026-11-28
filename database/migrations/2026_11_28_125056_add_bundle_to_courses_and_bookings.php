<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bundle pricing (KPR + civilná prvá pomoc): the two bundled courses and the link between
 * a bundle-priced booking and the earlier booking of the other bundled course.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->boolean('is_bundled')->default(false)->after('is_featured');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('bundle_booking_id')->nullable()->after('price')->constrained('bookings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('bundle_booking_id');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('is_bundled');
        });
    }
};
