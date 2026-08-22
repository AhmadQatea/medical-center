{{-- UI: Avatar — photo placeholder or initials --}}
@props([
    'name' => null,
    'initials' => null,
    'size' => 'md',
    'ring' => false,
])

@php
    $sizes = [
        'xs' => 'h-8 w-8 text-[11px]',
        'sm' => 'h-9 w-9 text-xs',
        'md' => 'h-12 w-12 text-sm',
        'lg' => 'h-16 w-16 text-lg',
        'xl' => 'h-24 w-24 text-2xl',
        '2xl' => 'h-28 w-28 text-3xl sm:h-32 sm:w-32',
    ];

    $mark = $initials
        ?? ($name ? mb_substr(preg_replace('/^(د\.|د\s)/u', '', $name), 0, 1) : 'م');
@endphp

<span
    {{ $attributes->class([
        'ds-avatar',
        $sizes[$size] ?? $sizes['md'],
        'ring-2 ring-accent ring-offset-2 ring-offset-background' => $ring,
    ]) }}
    role="img"
    @if ($name) aria-label="{{ $name }}" @endif
>
    {{ $slot->isEmpty() ? $mark : $slot }}
</span>
