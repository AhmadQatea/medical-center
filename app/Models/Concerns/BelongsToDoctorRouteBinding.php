<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Scope implicit route model binding to the authenticated doctor account.
 */
trait BelongsToDoctorRouteBinding
{
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field = $field ?: $this->getRouteKeyName();

        $query = $this->newQuery()->where($field, $value);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        return $query->first();
    }
}
