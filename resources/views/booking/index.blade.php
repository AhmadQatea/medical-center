@extends('layouts.booking')

@section('title', 'حجز موعد')

@section('content')
    <div class="pb-10">
        <div class="space-y-6">
            <x-booking.clinic-intro
                :clinic="$settings->clinic_name"
                :doctor="$doctor->name"
                :specialty="$settings->specialty"
                :description="$settings->description"
                :initials="$doctorInitials"
            />

            <div class="ds-gold-line" aria-hidden="true"></div>

            @if ($appointmentTypes->isEmpty())
                <x-booking.empty-state
                    title="لا توجد أنواع مواعيد متاحة حالياً"
                    description="يرجى المحاولة لاحقاً بعد تفعيل أنواع المواعيد من قبل العيادة."
                />
            @elseif (! $weeks['has_availability'])
                <x-booking.empty-state />
            @else
                <form
                    method="post"
                    action="{{ route('booking.store') }}"
                    class="space-y-7"
                    x-data="bookingFlow(@js($weeks), @js($appointmentTypes))"
                    x-on:submit="return onSubmit()"
                >
                    @csrf
                    <input type="hidden" name="date" x-model="selectedDate">
                    <input type="hidden" name="start_time" x-model="selectedTime">

                    <x-booking.schedule-picker />

                    <section class="space-y-3" x-show="selectedTime" x-transition x-cloak>
                        <x-booking.step-label step="4" title="بيانات المريض" />
                        <x-booking.guest-fields />
                        <x-booking.appointment-type-fields :types="$appointmentTypes" />
                    </section>

                    <section class="space-y-3" x-show="canSubmit || submitting" x-transition x-cloak>
                        <x-booking.step-label step="5" title="ملخص الحجز" />
                        <x-booking.summary />
                    </section>

                    <section class="space-y-3 sticky bottom-0 z-10 -mx-4 bg-gradient-to-t from-background via-background to-transparent px-4 pb-2 pt-4 sm:static sm:mx-0 sm:bg-none sm:px-0 sm:pb-0 sm:pt-0" x-show="selectedTime" x-cloak>
                        <x-booking.step-label step="6" title="تأكيد الحجز" class="sm:flex hidden" />
                        <p class="text-sm text-foreground-muted" x-show="! canSubmit && ! submitting" x-cloak>
                            <span x-show="missingFields.length === 0">أكمل البيانات المطلوبة لإرسال طلب الحجز</span>
                            <span x-show="missingFields.length > 0">
                                أكمل:
                                <span class="font-semibold text-foreground" x-text="missingFields.join('، ')"></span>
                            </span>
                        </p>
                        <button
                            type="submit"
                            class="inline-flex min-h-16 w-full items-center justify-center gap-3 rounded-2xl bg-primary px-8 text-lg font-semibold text-primary-foreground shadow-soft transition ds-press hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-muted focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                            x-bind:disabled="! canSubmit"
                        >
                            <template x-if="submitting">
                                <span class="inline-flex items-center gap-2">
                                    <span class="ds-spinner" aria-hidden="true"></span>
                                    جاري إرسال الطلب…
                                </span>
                            </template>
                            <template x-if="! submitting">
                                <span>تأكيد الحجز</span>
                            </template>
                        </button>
                    </section>
                </form>
            @endif
        </div>
    </div>
@endsection
