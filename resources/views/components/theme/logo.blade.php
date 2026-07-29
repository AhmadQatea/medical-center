{{--
    Theme Logo
    Renders clinic logo from --logo-url, with lettermark fallback.
--}}
@props([
    'alt' => config('app.name', 'Logo'),
    'letter' => null,
    'dark' => false,
])

@php
    $mark = $letter ?? mb_substr($alt, 0, 1);
@endphp

<span
    {{ $attributes->class([
        'inline-flex items-center justify-center overflow-hidden rounded-xl bg-primary text-sm font-bold text-primary-foreground',
        'theme-logo-dark' => $dark,
        'theme-logo' => ! $dark,
    ]) }}
    role="img"
    aria-label="{{ $alt }}"
>
    <span class="theme-logo-fallback">{{ $mark }}</span>
</span>
