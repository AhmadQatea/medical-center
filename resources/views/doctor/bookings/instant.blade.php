@extends('layouts.doctor')

@section('title', 'إضافة حجز يدوي')

@section('content')
    @php
        $ready = $bookingContext['state'] === \App\Services\AdminBookingContextService::STATE_READY;
        $contextQuery = app(\App\Services\AdminBookingContextService::class)->queryParams(
            $bookingContext['clinic'] ?? null,
            $bookingContext['doctor'] ?? null,
        );
    @endphp

    <x-layout.page-header
        title="إضافة حجز يدوي"
        description="{{ $ready ? $bookingContext['clinic']->name.' — '.$bookingContext['doctor']->name : 'قم بإدخال بيانات المريض واختيار الموعد المناسب' }}"
    />

    <div class="mx-auto max-w-3xl ds-stack">
        <x-doctor.clinic-doctor-context
            :context="$bookingContext"
            :action="route('doctor.bookings.instant')"
        />

        @if ($ready)
            @if (! $weeks['has_availability'])
                <x-booking.empty-state
                    title="لا توجد مواعيد متاحة"
                    description="لا توجد أوقات متاحة حالياً. راجع إدارة الجدول أو جرّب الأسبوع القادم."
                >
                    <x-ui.button href="{{ route('doctor.schedule.index', $contextQuery) }}" variant="soft" size="sm">
                        إدارة الجدول
                    </x-ui.button>
                </x-booking.empty-state>
            @else
                <form
                    method="post"
                    action="{{ route('doctor.bookings.store') }}"
                    class="space-y-6"
                    x-data="bookingFlow(@js($weeks), @js($appointmentTypes), {
                        defaultStatus: @js(old('status', 'confirmed')),
                        requireStatus: true,
                        initialName: @js(old('name', '')),
                        initialPhone: @js(old('phone', '')),
                        initialAppointmentTypeId: @js(old('appointment_type_id', '')),
                    })"
                    x-on:submit="onSubmit($event)"
                >
                    @csrf
                    <input type="hidden" name="clinic_id" value="{{ $bookingContext['clinic']->id }}">
                    <input type="hidden" name="doctor_id" value="{{ $bookingContext['doctor']->id }}">
                    <input type="hidden" name="date" x-model="selectedDate">
                    <input type="hidden" name="start_time" x-model="selectedTime">

                    <div class="ds-section-card">
                        <div class="ds-section-card-header">
                            <span class="ds-section-card-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </span>
                            <span>بيانات المريض</span>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-booking.guest-fields
                                name-label="اسم المريض"
                                name-placeholder="الاسم الكامل"
                            />
                        </div>
                    </div>

                    <div class="ds-section-card">
                        <div class="ds-section-card-header">
                            <span class="ds-section-card-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </span>
                            <span>تفاصيل الحجز</span>
                        </div>

                        <div class="space-y-6">
                            <x-booking.appointment-type-fields :types="$appointmentTypes" />

                            <x-form.select name="status" label="حالة الحجز" x-model="status">
                                <option value="confirmed">مؤكد</option>
                                <option value="pending">قيد الانتظار</option>
                            </x-form.select>

                            <div>
                                <x-booking.step-label step="1" title="التاريخ والوقت" />
                                <div class="mt-4">
                                    <x-booking.schedule-picker />
                                </div>
                            </div>
                        </div>
                    </div>

                    <section class="space-y-3" x-show="canSubmit || submitting" x-transition x-cloak>
                        <x-booking.step-label step="2" title="ملخص الموعد" />
                        <x-booking.summary />
                    </section>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button href="{{ route('doctor.bookings.index', $contextQuery) }}" variant="ghost" size="lg" class="w-full sm:w-auto">
                            إلغاء
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            size="lg"
                            class="w-full sm:w-auto"
                            x-bind:disabled="!canSubmit"
                            x-bind:loading="submitting"
                        >
                            تأكيد الحجز
                        </x-ui.button>
                    </div>
                </form>
            @endif
        @endif
    </div>
@endsection

@section('navbar-actions')
@endsection
