<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional billing details (name or company, address, IČO / DIČ) when they differ from the workshop address.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('billing_address')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('billing_address');
        });
    }
};
