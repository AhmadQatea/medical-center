{{-- Booking: This week / Next week selector --}}
@props([
    'model' => 'selectedWeek',
])

<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-2.5']) }} role="group" aria-label="اختر الأسبوع">
    <button
        type="button"
        x-on:click="{{ $model }} = 'this'; selectedDate = ''; selectedTime = ''"
        x-bind:aria-pressed="({{ $model }} === 'this').toString()"
        x-bind:class="{{ $model }} === 'this' ? 'is-selected' : ''"
        class="bk-day-chip bg-surface font-bold"
    >
        هذا الأسبوع
    </button>

    <button
        type="button"
        x-on:click="{{ $model }} = 'next'; selectedDate = ''; selectedTime = ''"
        x-bind:aria-pressed="({{ $model }} === 'next').toString()"
        x-bind:class="{{ $model }} === 'next' ? 'is-selected' : ''"
        class="bk-day-chip bg-surface font-bold"
    >
        الأسبوع القادم
    </button>
</div>
