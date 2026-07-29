{{--
    UI: Badge — semantic variants mapped to theme colors.
--}}
@props([
    'variant' => 'neutral',
    'size' => 'md',
])

@php
    $variants = [
        'neutral' => 'bg-surface-subtle text-foreground-muted',
        'primary' => 'bg-primary-soft text-primary',
        'success' => 'bg-success-soft text-success-foreground',
        'warning' => 'bg-warning-soft text-warning-foreground',
        'danger' => 'bg-danger-soft text-danger-foreground',
        'accent' => 'bg-accent-soft text-accent-foreground',
        'info' => 'bg-sky-100 text-sky-800',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[11px]',
        'md' => 'px-2.5 py-1 text-xs',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full font-medium '.($variants[$variant] ?? $variants['neutral']).' '.($sizes[$size] ?? $sizes['md']),
]) }}>
    {{ $slot }}
</span>
