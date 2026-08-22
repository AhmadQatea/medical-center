@extends('layouts.booking')

@section('title', 'حجز موعد')

@section('content')
    @php
        $backUrl = $autoSelectedDoctor ?? false
            ? route('booking.index')
            : route('booking.clinic', $clinic);
    @endphp

    @if (! $weeks['has_availability'])
        <x-booking.shell :step="3" title="حجز موعد" :subtitle="$clinic->name">
            <x-booking.empty-state />

            <div class="mt-4 grid grid-cols-2 gap-2">
                <dl class="bk-context-chip">
                    <dt>العيادة</dt>
                    <dd>{{ $clinic->name }}</dd>
                </dl>
                <dl class="bk-context-chip">
                    <dt>الطبيب</dt>
                    <dd>{{ $doctor->name }}</dd>
                </dl>
            </div>
        </x-booking.shell>
    @else
        <form
            method="post"
            action="{{ route('booking.store') }}"
            class="bk-shell"
            x-data="bookingFlow(@js($weeks), @js($appointmentTypes), {
                wizard: true,
                backUrl: @js($backUrl),
                initialName: @js(old('name', '')),
                initialPhone: @js(old('phone', '')),
                initialAppointmentTypeId: @js(old('appointment_type_id', '')),
            })"
            x-on:submit="onSubmit($event)"
        >
            @csrf
            <input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
            <input type="hidden" name="date" x-model="selectedDate">
            <input type="hidden" name="start_time" x-model="selectedTime">

            <header class="bk-header">
                <div class="bk-header-inner">
                    <x-theme.logo :alt="config('clinic.brand.name')" letter="C" class="h-9 w-9 rounded-xl text-sm" />
                    <span class="bk-brand-title">{{ config('clinic.brand.name') }}</span>
                </div>

                <div class="bk-progress-segments mt-3" role="progressbar" aria-label="تقدم الحجز">
                    <template x-for="segment in 6" :key="segment">
                        <span
                            class="bk-progress-segment"
                            x-bind:class="{
                                'is-active': segment < globalStep,
                                'is-current': segment === globalStep,
                            }"
                            aria-hidden="true"
                        ></span>
                    </template>
                </div>

                <div class="bk-page-title mt-4">
                    <h1 x-text="stepTitle"></h1>
                    <p>{{ $clinic->name }} — {{ $doctor->name }}</p>
                </div>
            </header>

            <div class="bk-content">
                <div class="mb-4 grid grid-cols-2 gap-2">
                    <dl class="bk-context-chip">
                        <dt>العيادة</dt>
                        <dd>{{ $clinic->name }}</dd>
                    </dl>
                    <dl class="bk-context-chip">
                        <dt>الطبيب</dt>
                        <dd>{{ $doctor->name }}</dd>
                    </dl>
                </div>

                @if ($autoSelectedDoctor ?? false)
                    <p class="mb-4 text-center text-xs text-foreground-muted">تم اختيار الطبيب تلقائياً</p>
                @endif

                <section x-show="currentStep === 1" x-cloak class="space-y-3">
                    <x-booking.service-picker :types="$appointmentTypes" />
                </section>

                <section x-show="currentStep === 2" x-cloak>
                    <x-booking.schedule-picker />
                </section>

                <section x-show="currentStep === 3" x-cloak class="space-y-4">
                    <x-booking.guest-fields />
                </section>

                <section x-show="currentStep === 4" x-cloak class="space-y-4">
                    <p class="text-sm text-foreground-muted">راجع تفاصيل الحجز قبل الإرسال — مراجعة</p>
                    <x-booking.review-summary :clinic="$clinic->name" :doctor="$doctor->name" />

                    <p class="text-sm text-foreground-muted" x-show="! canSubmit && ! submitting" x-cloak>
                        <span x-show="missingFields.length === 0">اضغط تأكيد الحجز لإرسال الطلب</span>
                        <span x-show="missingFields.length > 0">
                            أكمل:
                            <span class="font-semibold text-foreground" x-text="missingFields.join('، ')"></span>
                        </span>
                    </p>
                </section>

                <span class="sr-only">اختر الأسبوع</span>
            </div>

            <footer class="bk-footer">
                <x-booking.footer-actions>
                    <x-slot:next>
                        <button
                            type="button"
                            class="bk-btn bk-btn-primary"
                            x-show="currentStep < 4"
                            x-on:click="nextStep()"
                            x-bind:disabled="! canGoNext"
                            x-cloak
                        >
                            التالي
                        </button>

                        <button
                            type="submit"
                            class="bk-btn bk-btn-confirm"
                            x-show="currentStep === 4"
                            x-bind:disabled="! canSubmit"
                            x-cloak
                        >
                            <span x-show="submitting" class="inline-flex items-center gap-2">
                                <span class="ds-spinner" aria-hidden="true"></span>
                                جاري إرسال الطلب…
                            </span>
                            <span x-show="! submitting">تأكيد الحجز</span>
                        </button>
                    </x-slot:next>
                    <x-slot:back>
                        <button type="button" class="bk-btn bk-btn-soft" x-on:click="prevStep()">
                            السابق
                        </button>
                    </x-slot:back>
                </x-booking.footer-actions>
            </footer>
        </form>
    @endif
@endsection
