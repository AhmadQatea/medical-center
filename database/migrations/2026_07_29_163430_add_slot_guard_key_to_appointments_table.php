<?php

use App\Support\BookingSlotKey;
use App\Support\TimeFormat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('slot_guard_key', 40)
                ->nullable()
                ->after('start_time')
                ->comment('Set when status is pending|confirmed; enforces unique active slot');

            $table->unique(['user_id', 'slot_guard_key'], 'appointments_user_slot_guard_unique');
        });

        DB::table('appointments')
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('id')
            ->each(function (object $row): void {
                DB::table('appointments')
                    ->where('id', $row->id)
                    ->update([
                        'slot_guard_key' => BookingSlotKey::for(
                            (string) $row->date,
                            TimeFormat::normalize((string) $row->start_time),
                        ),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_user_slot_guard_unique');
            $table->dropColumn('slot_guard_key');
        });
    }
};
