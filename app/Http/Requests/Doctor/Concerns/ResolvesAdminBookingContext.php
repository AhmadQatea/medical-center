<?php

namespace App\Http\Requests\Doctor\Concerns;

use App\Models\User;
use App\Services\AdminBookingContextService;

trait ResolvesAdminBookingContext
{
    public function targetDoctor(): User
    {
        return app(AdminBookingContextService::class)->resolveTargetDoctor(
            $this->user(),
            $this->filled('clinic_id') ? (int) $this->input('clinic_id') : null,
            $this->filled('doctor_id') ? (int) $this->input('doctor_id') : null,
        );
    }
}
