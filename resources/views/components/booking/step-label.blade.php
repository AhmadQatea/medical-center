{{--
    Booking: Step Label
    Numbered step heading for the booking journey.
--}}
@props([
    'step',
    'title',
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground" aria-hidden="true">
        {{ $step }}
    </span>
    <h2 class="text-base font-bold text-foreground">{{ $title }}</h2>
</div>
