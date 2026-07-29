{{-- Booking: large day button --}}
@props([
    'date',
    'weekday',
    'dayLabel',
    'model' => 'selectedDate',
])

<button
    type="button"
    x-on:click="{{ $model }} = '{{ $date }}'; selectedTime = ''"
    x-bind:class="{{ $model }} === '{{ $date }}'
        ? 'border-primary bg-primary text-primary-foreground shadow-soft'
        : 'border-border bg-surface text-foreground hover:border-primary-muted hover:bg-primary-soft'"
    {{ $attributes->merge(['class' => 'flex min-h-20 w-full flex-col items-start justify-center rounded-2xl border px-5 py-4 text-start transition']) }}
>
    <span class="text-lg font-bold">{{ $weekday }}</span>
    <span class="mt-1 text-base opacity-80">{{ $dayLabel }}</span>
</button>
