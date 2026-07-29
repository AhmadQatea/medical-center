{{-- Layout: Container — comfortable mobile padding --}}
@props([
    'width' => 'default',
])

@php
    $widths = [
        'narrow' => 'max-w-lg',
        'booking' => 'max-w-md',
        'default' => 'max-w-6xl',
        'wide' => 'max-w-7xl',
        'full' => 'max-w-none',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'mx-auto w-full px-4 py-5 pb-8 sm:px-6 sm:py-8 lg:px-8 '.$widths[$width]]) }}>
    {{ $slot }}
</div>
