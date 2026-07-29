{{-- Booking: This week / Next week selector --}}
@props([
    'model' => 'selectedWeek',
])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-3 sm:grid-cols-2']) }} role="group" aria-label="اختر الأسبوع">
    <button
        type="button"
        x-on:click="{{ $model }} = 'this'; selectedDate = ''; selectedTime = ''"
        x-bind:aria-pressed="({{ $model }} === 'this').toString()"
        x-bind:class="{{ $model }} === 'this'
            ? 'border-primary bg-primary text-primary-foreground shadow-soft'
            : 'border-border bg-surface text-foreground hover:border-primary-muted hover:bg-primary-soft'"
        class="ds-press min-h-16 rounded-2xl border px-5 text-lg font-bold transition"
    >
        هذا الأسبوع
    </button>

    <button
        type="button"
        x-on:click="{{ $model }} = 'next'; selectedDate = ''; selectedTime = ''"
        x-bind:aria-pressed="({{ $model }} === 'next').toString()"
        x-bind:class="{{ $model }} === 'next'
            ? 'border-primary bg-primary text-primary-foreground shadow-soft'
            : 'border-border bg-surface text-foreground hover:border-primary-muted hover:bg-primary-soft'"
        class="ds-press min-h-16 rounded-2xl border px-5 text-lg font-bold transition"
    >
        الأسبوع القادم
    </button>
</div>
