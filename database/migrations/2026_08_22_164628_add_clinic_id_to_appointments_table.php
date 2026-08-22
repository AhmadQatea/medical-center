<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Denormalized clinic on appointments for filtering and historical context.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('clinic_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->index(['clinic_id', 'date']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropIndex(['clinic_id', 'date']);
            $table->dropIndex(['clinic_id', 'status']);
            $table->dropIndex(['clinic_id', 'user_id', 'date']);
            $table->dropColumn('clinic_id');
        });
    }
};
