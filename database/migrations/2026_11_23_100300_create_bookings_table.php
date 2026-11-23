<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One booking reserves a whole slot (date + morning/afternoon) for one group of 1-4 people.
     *
     * The active_slot column holds "YYYY-MM-DD:slot" while the booking is active and NULL once it
     * is cancelled. Its unique index makes double booking impossible even under concurrent requests.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->string('time_slot', 20);
            $table->string('status', 20)->index();
            $table->string('active_slot', 32)->nullable()->unique();
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('address');
            $table->unsignedTinyInteger('participants');
            $table->text('note')->nullable();
            $table->decimal('price', 8, 2);
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_version', 20)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('anonymized_at')->nullable();
            $table->timestamps();

            $table->index(['date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
