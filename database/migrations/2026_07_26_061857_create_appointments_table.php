<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Core appointments for Instant Booking, Bookings, Timeline, Dashboard, and public booking.
     * Active-slot conflicts are enforced in application transactions
     * (cancelled rows may reuse a slot).
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Doctor who owns the appointment');

            $table->foreignId('patient_id')
                ->constrained()
                ->restrictOnDelete()
                ->comment('Soft-delete patients instead of hard-deleting booked ones');

            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')
                ->comment('Typically start_time + appointment_duration_minutes');

            // String columns (not MySQL ENUM) so PHP enums can evolve without ALTER ENUMs.
            $table->string('status', 20)
                ->default('confirmed')
                ->comment('pending|confirmed|completed|cancelled|no_show');

            $table->string('source', 20)
                ->default('instant')
                ->comment('instant|public|whatsapp');

            $table->text('notes')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'date', 'start_time']);
            $table->index(['user_id', 'status']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
