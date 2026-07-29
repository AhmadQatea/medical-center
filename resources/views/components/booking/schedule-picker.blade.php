{{-- Booking: week → day → time step picker (shared public + doctor instant) --}}

<section class="space-y-3" aria-labelledby="booking-step-week">
    <x-booking.step-label step="1" title="اختر الأسبوع" id="booking-step-week" />
    <x-booking.week-selector />
</section>

<section class="space-y-3" x-show="currentDays.length > 0" x-cloak aria-labelledby="booking-step-day">
    <x-booking.step-label step="2" title="اختر اليوم" id="booking-step-day" />
    <div class="grid grid-cols-1 gap-3" role="listbox" aria-label="أيام متاحة">
        <template x-for="day in currentDays" :key="day.date">
            <button
                type="button"
                role="option"
                x-on:click="selectDay(day)"
                x-bind:aria-selected="(selectedDate === day.date).toString()"
                x-bind:class="selectedDate === day.date
                    ? 'border-primary bg-primary text-primary-foreground shadow-soft'
                    : 'border-border bg-surface text-foreground hover:border-primary-muted hover:bg-primary-soft'"
                class="ds-press flex min-h-20 w-full flex-col items-start justify-center rounded-2xl border px-5 py-4 text-start transition"
            >
                <span class="text-lg font-bold" x-text="day.weekday_label"></span>
                <span class="mt-1 text-base opacity-80" x-text="day.day_label"></span>
            </button>
        </template>
    </div>
</section>

<section class="space-y-3" x-show="selectedDate && currentTimes.length > 0" x-cloak aria-labelledby="booking-step-time">
    <x-booking.step-label step="3" title="اختر الوقت" id="booking-step-time" />
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3" role="listbox" aria-label="أوقات متاحة">
        <template x-for="slot in currentTimes" :key="slot.value">
            <button
                type="button"
                role="option"
                x-on:click="selectTime(slot)"
                x-bind:aria-selected="(selectedTime === slot.value).toString()"
                x-bind:class="selectedTime === slot.value
                    ? 'border-primary bg-primary text-primary-foreground shadow-soft'
                    : 'border-border bg-surface text-foreground hover:border-primary-muted hover:bg-primary-soft'"
                class="ds-press flex min-h-16 w-full items-center justify-center rounded-2xl border text-lg font-bold transition"
            >
                <span x-text="slot.label"></span>
            </button>
        </template>
    </div>
</section>
