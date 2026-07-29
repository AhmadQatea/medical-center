<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clinic identity for Clinic Settings and the public booking page.
     * One row per doctor (users).
     */
    public function up(): void
    {
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Owning doctor; one clinic profile per user');

            $table->string('clinic_name')->comment('Public clinic name');
            $table->string('specialty', 100)->default('Dentist');
            $table->text('description')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('whatsapp_number', 20)->comment('E.164-style number for WhatsApp booking');
            $table->string('logo_path')->nullable()->comment('Stored logo path relative to disk');
            $table->string('photo_path')->nullable()->comment('Doctor photo path relative to disk');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
    }
};
