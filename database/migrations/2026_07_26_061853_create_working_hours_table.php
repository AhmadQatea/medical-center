<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-weekday open/closed state and working hours.
     * Expect seven rows per doctor (one per weekday).
     */
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('weekday')
                ->comment('0 = Sunday … 6 = Saturday');

            $table->boolean('is_open')->default(true);

            $table->time('start_time')
                ->nullable()
                ->comment('Null when the day is closed');

            $table->time('end_time')
                ->nullable()
                ->comment('Null when the day is closed; must be after start_time when open');

            $table->timestamps();

            $table->unique(['user_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
