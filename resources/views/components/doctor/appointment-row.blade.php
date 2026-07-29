{{-- Doctor: Appointment row --}}
@props([
    'time',
    'patient',
    'note' => null,
    'phone' => null,
    'status' => 'confirmed',
    'current' => false,
    'appointment' => null,
])

@php
    $statusEnum = is_string($status) ? \App\Enums\AppointmentStatus::tryFrom($status) : null;
    $label = $statusEnum?->label() ?? $status;
    $variant = $statusEnum?->color() ?? 'primary';
    $timeLabel = (is_string($time) && preg_match('/(صباحاً|مساءً|ظهراً)/u', $time))
        ? $time
        : (is_string($time) ? \App\Support\TimeFormat::arabic($time) : $time);
@endphp

<div
    {{ $attributes->class([
        'flex gap-4 rounded-2xl border border-border bg-surface p-4 shadow-soft sm:p-5',
        'ring-2 ring-primary/20 border-primary' => $current,
    ]) }}
>
    <div class="flex w-20 shrink-0 flex-col items-center gap-2 sm:w-24">
        <span class="text-center text-sm font-bold leading-tight text-foreground sm:text-base">{{ $timeLabel }}</span>
        <span @class([
            'h-full w-0.5 grow rounded-full',
            'bg-primary' => $current,
            'bg-border' => ! $current,
        ]) aria-hidden="true"></span>
    </div>

    <div class="min-w-0 flex-1 space-y-2">
        <div class="flex flex-wrap items-center gap-2">
            <p class="font-bold text-foreground">{{ $patient }}</p>
            <x-ui.badge :variant="$variant" size="sm">{{ $label }}</x-ui.badge>
        </div>
        @if ($note)
            <p class="text-sm text-foreground-muted">{{ $note }}</p>
        @endif
        @if ($phone)
            <p class="text-sm text-foreground-subtle" dir="ltr">+{{ ltrim($phone, '+') }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
