<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global schedule rules: appointment length, gaps, and lunch break.
     * Available slots are computed at runtime — not stored.
     */
    public function up(): void
    {
        Schema::create('schedule_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Owning doctor; one schedule config per user');

            $table->unsignedTinyInteger('appointment_duration_minutes')
                ->default(30)
                ->comment('Length of each appointment slot in minutes');

            $table->unsignedTinyInteger('break_duration_minutes')
                ->default(0)
                ->comment('Optional gap between consecutive slots');

            $table->boolean('lunch_enabled')->default(true);

            $table->time('lunch_start')
                ->nullable()
                ->comment('Lunch window start; required in app when lunch_enabled');

            $table->time('lunch_end')
                ->nullable()
                ->comment('Lunch window end; must be after lunch_start');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_settings');
    }
};
