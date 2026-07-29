{{-- Booking: large selectable time slot --}}
@props([
    'time' => null,
    'selected' => false,
    'disabled' => false,
])

@php
    $classes = 'flex min-h-16 w-full items-center justify-center rounded-2xl border text-lg font-bold transition '
        .'data-[selected=true]:border-primary data-[selected=true]:bg-primary data-[selected=true]:text-primary-foreground '
        .'border-border bg-surface text-foreground hover:border-primary-muted hover:bg-primary-soft hover:text-primary '
        .'disabled:cursor-not-allowed disabled:opacity-50';
@endphp

<button
    type="button"
    @if ($selected) data-selected="true" @endif
    @disabled($disabled)
    {{ $attributes->merge(['class' => $classes]) }}
>
    <span dir="ltr">{{ $time }}</span>
</button>
