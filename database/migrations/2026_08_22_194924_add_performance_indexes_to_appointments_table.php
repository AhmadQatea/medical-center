<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes for center-wide dashboard / booking list filters by date + status.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['date', 'status'], 'appointments_date_status_index');
            $table->index(['status', 'date'], 'appointments_status_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_date_status_index');
            $table->dropIndex('appointments_status_date_index');
        });
    }
};
