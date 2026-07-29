{{-- UI: Stat Card — premium metric tile --}}
@props([
    'label',
    'value',
    'hint' => null,
    'variant' => 'primary',
])

@php
    $accents = [
        'primary' => 'bg-primary-soft text-primary',
        'accent' => 'bg-accent-soft text-accent-foreground',
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'neutral' => 'bg-surface-subtle text-foreground-muted',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'ds-surface p-4 sm:p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1.5">
            <p class="text-xs font-medium text-foreground-muted sm:text-sm">{{ $label }}</p>
            <p class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">{{ $value }}</p>
            @if ($hint)
                <p class="text-xs text-foreground-subtle">{{ $hint }}</p>
            @endif
            {{ $slot }}
        </div>

        <div @class([
            'flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl sm:h-11 sm:w-11',
            $accents[$variant] ?? $accents['primary'],
        ])>
            @isset($icon)
                {{ $icon }}
            @else
                <span class="text-sm font-bold">{{ mb_substr((string) $value, 0, 1) }}</span>
            @endisset
        </div>
    </div>
</div>
