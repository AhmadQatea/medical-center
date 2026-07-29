<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('appointment_type', 30)
                ->nullable()
                ->after('source')
                ->comment('Dental visit type (see AppointmentType enum)');

            $table->string('appointment_type_other', 100)
                ->nullable()
                ->after('appointment_type')
                ->comment('Free text when appointment_type is other');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['appointment_type', 'appointment_type_other']);
        });
    }
};
