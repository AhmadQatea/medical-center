<?php

use App\Models\User;
use App\Services\AppointmentTypeService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $service = app(AppointmentTypeService::class);

        User::query()
            ->where(function ($query): void {
                $query->where('role', 'doctor')
                    ->orWhere('role', 'admin');
            })
            ->each(fn (User $doctor) => $service->ensureForDoctor($doctor));
    }

    public function down(): void
    {
        //
    }
};
