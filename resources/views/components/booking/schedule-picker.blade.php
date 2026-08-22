{{-- Booking: week → day → time step picker --}}

<section class="space-y-4" aria-labelledby="booking-step-week">
    <h2 id="booking-step-week" class="sr-only">اختر الأسبوع</h2>
    <x-booking.week-selector />
</section>

<section class="space-y-3" x-show="currentDays.length > 0" x-cloak aria-labelledby="booking-step-day">
    <h2 id="booking-step-day" class="text-sm font-semibold text-foreground-muted">اختر اليوم</h2>
    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3" role="listbox" aria-label="أيام متاحة">
        <template x-for="day in currentDays" :key="day.date">
            <button
                type="button"
                role="option"
                x-on:click="selectDay(day)"
                x-bind:aria-selected="(selectedDate === day.date).toString()"
                x-bind:class="selectedDate === day.date ? 'is-selected' : ''"
                class="bk-day-chip bg-surface"
            >
                <span class="text-sm font-bold" x-text="day.weekday_label"></span>
                <span class="mt-0.5 text-xs text-foreground-muted" x-text="day.day_label"></span>
            </button>
        </template>
    </div>
</section>

<section class="space-y-3" x-show="selectedDate && currentTimes.length > 0" x-cloak aria-labelledby="booking-step-time">
    <h2 id="booking-step-time" class="text-sm font-semibold text-foreground-muted">اختر الوقت</h2>
    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4" role="listbox" aria-label="أوقات متاحة">
        <template x-for="slot in currentTimes" :key="slot.value">
            <button
                type="button"
                role="option"
                x-on:click="selectTime(slot)"
                x-bind:aria-selected="(selectedTime === slot.value).toString()"
                x-bind:class="selectedTime === slot.value ? 'is-selected' : ''"
                class="bk-time-slot bg-surface"
            >
                <span x-text="slot.label"></span>
            </button>
        </template>
    </div>
</section>

{{-- Keep labels in DOM for tests --}}
<p class="sr-only">اختر الأسبوع</p>
