<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Doctors (users) belong to a clinic; admins manage the whole center.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clinic_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->string('role', 20)
                ->default('doctor')
                ->after('email')
                ->comment('admin|doctor');

            $table->boolean('is_active')
                ->default(true)
                ->after('role');

            $table->string('specialty')->nullable()->after('is_active');
            $table->string('photo_path')->nullable()->after('specialty');
            $table->unsignedSmallInteger('display_order')->default(0)->after('photo_path');

            $table->index(['clinic_id', 'is_active', 'display_order']);
            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropIndex(['clinic_id', 'is_active', 'display_order']);
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn([
                'clinic_id',
                'role',
                'is_active',
                'specialty',
                'photo_path',
                'display_order',
            ]);
        });
    }
};
