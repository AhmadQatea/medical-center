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
            $user = auth()->user();

            if (! $user->isAdmin()) {
                $query->where('user_id', $user->id);
            }
        }

        return $query->first();
    }
}
