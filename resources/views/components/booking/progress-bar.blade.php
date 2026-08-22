{{-- Booking: segmented progress (6 steps) --}}
@props([
    'current' => 1,
    'total' => 6,
])

@php
    $current = max(1, min((int) $current, (int) $total));
@endphp

<div {{ $attributes->merge(['class' => 'bk-progress-segments']) }} role="progressbar" aria-valuenow="{{ $current }}" aria-valuemin="1" aria-valuemax="{{ $total }}" aria-label="تقدم الحجز">
    @for ($i = 1; $i <= $total; $i++)
        <span @class([
            'bk-progress-segment',
            'is-active' => $i < $current,
            'is-current' => $i === $current,
        ]) aria-hidden="true"></span>
    @endfor
</div>
