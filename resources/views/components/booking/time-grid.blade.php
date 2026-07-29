{{-- Booking: Time Grid — large touch-friendly slots --}}
@props([
    'times' => [],
    'model' => 'selectedTime',
    'interactive' => true,
])

<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-3 sm:grid-cols-3']) }}>
    @foreach ($times as $time)
        @if ($interactive)
            <x-booking.time-slot-button
                :time="$time"
                x-bind:data-selected="{{ $model }} === '{{ $time }}'"
                x-on:click="{{ $model }} = '{{ $time }}'"
                x-bind:class="{{ $model }} === '{{ $time }}'
                    ? 'border-primary bg-primary text-primary-foreground shadow-soft'
                    : ''"
            />
        @else
            <x-booking.time-slot-button
                :time="$time"
                disabled
                class="pointer-events-none opacity-90"
            />
        @endif
    @endforeach
</div>
