<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'appointment_type')) {
                $table->dropColumn(['appointment_type', 'appointment_type_other']);
            }

            $table->foreignId('appointment_type_id')
                ->nullable()
                ->after('source')
                ->constrained('appointment_types')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('appointment_type_id');

            $table->string('appointment_type', 30)->nullable()->after('source');
            $table->string('appointment_type_other', 100)->nullable()->after('appointment_type');
        });
    }
};
