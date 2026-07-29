{{-- UI: Color badge for dynamic appointment types --}}
@props([
    'name',
    'color' => null,
])

@php
    $style = filled($color)
        ? 'background-color: '.$color.'20; color: '.$color.'; border-color: '.$color.'40;'
        : '';
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full border border-border px-2.5 py-1 text-xs font-semibold',
    'style' => $style,
]) }}>
    {{ $name }}
</span>
