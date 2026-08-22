@extends('layouts.doctor')

@section('title', 'إدارة الجدول')

@section('navbar-actions')
    @if (($bookingContext['state'] ?? null) === \App\Services\AdminBookingContextService::STATE_READY)
        <x-ui.button type="submit" form="schedule-form" variant="primary" size="sm">حفظ</x-ui.button>
    @endif
@endsection

@section('content')
    @php
        $ready = $bookingContext['state'] === \App\Services\AdminBookingContextService::STATE_READY;
        $formatTime = static function (?string $time): string {
            if ($time === null || $time === '') {
                return '';
            }

            return strlen($time) >= 5 ? substr($time, 0, 5) : $time;
        };
    @endphp

    <x-layout.page-header
        title="إدارة الجدول"
        description="{{ $ready ? $bookingContext['clinic']->name.' — '.$bookingContext['doctor']->name : 'اختر العيادة والطبيب لإدارة الجدول' }}"
    />

    <div class="ds-stack">
        <x-doctor.clinic-doctor-context
            :context="$bookingContext"
            :action="route('doctor.schedule.index')"
        />

        @if ($ready)
            @if (! $hasOpenDays)
                <x-ui.alert variant="warning">
                    لم يتم إعداد جدول مواعيد لهذا الطبيب. فعّل أيام العمل أدناه.
                </x-ui.alert>
            @endif

            <form
                id="schedule-form"
                method="post"
                action="{{ route('doctor.schedule.update') }}"
                class="ds-stack"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="clinic_id" value="{{ $bookingContext['clinic']->id }}">
                <input type="hidden" name="doctor_id" value="{{ $bookingContext['doctor']->id }}">

        <x-ui.section title="أيام وساعات العمل" description="فعّل اليوم ثم حدد بداية ونهاية الدوام">
            <div class="space-y-3">
                @foreach ($workingHours as $index => $day)
                    @php
                        $weekday = (int) $day->weekday;
                        $isOpen = (bool) old("days.{$index}.is_open", $day->is_open);
                        $start = old("days.{$index}.start_time", $formatTime($day->start_time ? (string) $day->start_time : null));
                        $end = old("days.{$index}.end_time", $formatTime($day->end_time ? (string) $day->end_time : null));
                    @endphp

                    <x-ui.card>
                        <div class="space-y-4" x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }">
                            <input type="hidden" name="days[{{ $index }}][weekday]" value="{{ $weekday }}">

                            <label class="flex cursor-pointer items-center justify-between gap-3">
                                <span class="text-base font-bold text-foreground">
                                    {{ $scheduleService->weekdayLabel($weekday) }}
                                </span>
                                <span class="relative inline-flex">
                                    <input
                                        type="checkbox"
                                        name="days[{{ $index }}][is_open]"
                                        value="1"
                                        class="peer sr-only"
                                        x-model="open"
                                        @checked($isOpen)
                                    >
                                    <span class="h-7 w-12 rounded-full bg-surface-subtle transition peer-checked:bg-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary-muted"></span>
                                    <span class="pointer-events-none absolute start-0.5 top-0.5 h-6 w-6 rounded-full bg-surface shadow-soft transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                                </span>
                            </label>

                            <div class="grid gap-4 sm:grid-cols-2" x-show="open" x-cloak>
                                <x-form.input
                                    :name="'days['.$index.'][start_time]'"
                                    type="time"
                                    label="بداية الدوام"
                                    :value="$start"
                                    dir="ltr"
                                    class="!min-h-14"
                                />
                                <x-form.input
                                    :name="'days['.$index.'][end_time]'"
                                    type="time"
                                    label="نهاية الدوام"
                                    :value="$end"
                                    dir="ltr"
                                    class="!min-h-14"
                                />
                            </div>

                            @error("days.{$index}.start_time")
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                            @error("days.{$index}.end_time")
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        </x-ui.section>

        <div class="ds-grid-2">
            <x-ui.section title="مدة الموعد" description="طول كل كشف">
                <x-ui.card>
                    <x-form.select name="appointment_duration_minutes" label="المدة بالدقائق" required>
                        @foreach ([15, 20, 30, 45, 60] as $minutes)
                            <option
                                value="{{ $minutes }}"
                                @selected((int) old('appointment_duration_minutes', $settings->appointment_duration_minutes) === $minutes)
                            >{{ $minutes }} دقيقة</option>
                        @endforeach
                    </x-form.select>
                </x-ui.card>
            </x-ui.section>

            <x-ui.section title="فاصل بين المواعيد" description="استراحة اختيارية بين كل موعدين">
                <x-ui.card>
                    <x-form.select name="break_duration_minutes" label="الفاصل بالدقائق">
                        @foreach ([0, 5, 10, 15] as $minutes)
                            <option
                                value="{{ $minutes }}"
                                @selected((int) old('break_duration_minutes', $settings->break_duration_minutes) === $minutes)
                            >
                                {{ $minutes === 0 ? 'بدون فاصل' : $minutes.' دقائق' }}
                            </option>
                        @endforeach
                    </x-form.select>
                </x-ui.card>
            </x-ui.section>
        </div>

        <x-ui.section title="استراحة الغداء" description="فترة يومية تُستثنى من الحجز">
            <x-ui.card>
                <div
                    class="space-y-5"
                    x-data="{ lunch: {{ old('lunch_enabled', $settings->lunch_enabled) ? 'true' : 'false' }} }"
                >
                    <label class="inline-flex cursor-pointer items-center gap-3">
                        <span class="relative inline-flex">
                            <input
                                type="checkbox"
                                name="lunch_enabled"
                                value="1"
                                class="peer sr-only"
                                x-model="lunch"
                                @checked(old('lunch_enabled', $settings->lunch_enabled))
                            >
                            <span class="h-7 w-12 rounded-full bg-surface-subtle transition peer-checked:bg-primary"></span>
                            <span class="pointer-events-none absolute start-0.5 top-0.5 h-6 w-6 rounded-full bg-surface shadow-soft transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                        </span>
                        <span class="text-sm font-medium text-foreground">تفعيل استراحة الغداء</span>
                    </label>

                    <div class="grid gap-5 sm:grid-cols-2" x-show="lunch" x-cloak>
                        <x-form.input
                            name="lunch_start"
                            type="time"
                            label="من"
                            :value="old('lunch_start', $formatTime($settings->lunch_start ? (string) $settings->lunch_start : null))"
                            dir="ltr"
                            class="!min-h-14"
                        />
                        <x-form.input
                            name="lunch_end"
                            type="time"
                            label="إلى"
                            :value="old('lunch_end', $formatTime($settings->lunch_end ? (string) $settings->lunch_end : null))"
                            dir="ltr"
                            class="!min-h-14"
                        />
                    </div>
                </div>
            </x-ui.card>
        </x-ui.section>

        <x-ui.button type="submit" variant="primary" size="xl" class="w-full sm:w-auto">
            حفظ إدارة الجدول
        </x-ui.button>
            </form>

            <div class="ds-stack mt-8">
        <x-ui.section title="الإجازات" description="أيام مغلقة بالكامل عن الحجز">
            <x-ui.card>
                <div class="ds-list-stack">
                    @forelse ($holidays as $holiday)
                        <div class="flex flex-col gap-3 rounded-2xl border border-border bg-surface-subtle p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-foreground">
                                    {{ $holiday->title ?: 'إجازة' }}
                                </p>
                                <p class="text-sm text-foreground-muted">
                                    <span dir="ltr">{{ $holiday->date->toDateString() }}</span>
                                    — مغلق للحجز
                                    @if ($holiday->note)
                                        · {{ $holiday->note }}
                                    @endif
                                </p>
                            </div>
                            <form method="post" action="{{ route('doctor.schedule.holidays.destroy', $holiday) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="ghost" size="sm">حذف</x-ui.button>
                            </form>
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="لا توجد إجازات"
                            description="أضف يوماً مغلقاً عندما يكون الطبيب غير متاح للحجز."
                            class="!border-0 !bg-transparent !px-0 !py-4"
                        />
                    @endforelse

                    <form method="post" action="{{ route('doctor.schedule.holidays.store') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                        @csrf
                        <input type="hidden" name="clinic_id" value="{{ $bookingContext['clinic']->id }}">
                        <input type="hidden" name="doctor_id" value="{{ $bookingContext['doctor']->id }}">
                        <x-form.input name="date" type="date" label="تاريخ إجازة جديدة" dir="ltr" required class="!min-h-14" />
                        <x-form.input name="title" label="العنوان" placeholder="مثال: إجازة شخصية" class="!min-h-14" />
                        <div class="flex items-end">
                            <x-ui.button type="submit" variant="soft" class="w-full sm:w-auto min-h-14">إضافة</x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
        </x-ui.section>

        <div class="sticky bottom-0 z-10 -mx-4 border-t border-border bg-background/95 px-4 py-3 backdrop-blur-md ds-safe-bottom sm:hidden">
            <x-ui.button type="submit" form="schedule-form" variant="primary" size="lg" class="w-full">
                حفظ الجدول
            </x-ui.button>
        </div>
            </div>
        @endif
    </div>
@endsection
