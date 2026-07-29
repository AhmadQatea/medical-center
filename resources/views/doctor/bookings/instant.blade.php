@extends('layouts.doctor')

@section('title', 'حجز فوري')

@section('content')
    <x-layout.page-header
        title="حجز فوري"
        description="اختر الموعد ثم أدخل بيانات المريض"
    />

    <div class="mx-auto max-w-xl">
        @if ($appointmentTypes->isEmpty())
            <x-ui.empty-state
                title="لا توجد أنواع مواعيد متاحة"
                description="أنشئ أنواع مواعيد نشطة قبل إنشاء حجز فوري."
                icon="tag"
            >
                <x-ui.button href="{{ route('doctor.appointment-types.create') }}" variant="soft" size="sm">
                    إنشاء نوع موعد
                </x-ui.button>
            </x-ui.empty-state>
        @elseif (! $weeks['has_availability'])
            <x-booking.empty-state
                title="لا توجد مواعيد متاحة"
                description="لا توجد أوقات متاحة حالياً. راجع إدارة الجدول أو جرّب الأسبوع القادم."
            >
                <x-ui.button href="{{ route('doctor.schedule.index') }}" variant="soft" size="sm">
                    إدارة الجدول
                </x-ui.button>
            </x-booking.empty-state>
        @else
            <form
                method="post"
                action="{{ route('doctor.bookings.store') }}"
                class="space-y-7"
                x-data="bookingFlow(@js($weeks), @js($appointmentTypes), { defaultStatus: @js(old('status', 'confirmed')), requireStatus: true })"
                x-on:submit="return onSubmit()"
            >
                @csrf

                <input type="hidden" name="date" x-model="selectedDate">
                <input type="hidden" name="start_time" x-model="selectedTime">

                <x-booking.schedule-picker />

                <section class="space-y-4" x-show="selectedTime" x-transition x-cloak>
                    <x-booking.step-label step="4" title="بيانات المريض" />
                    <x-ui.card>
                        <div class="space-y-4">
                            <x-booking.guest-fields
                                name-label="اسم المريض"
                                name-placeholder="الاسم الكامل"
                            />
                            <x-booking.appointment-type-fields :types="$appointmentTypes" />
                            <x-form.select name="status" label="الحالة" x-model="status" class="!min-h-16 !rounded-2xl !text-lg">
                                <option value="confirmed">مؤكد</option>
                                <option value="pending">قيد الانتظار</option>
                            </x-form.select>
                        </div>
                    </x-ui.card>
                </section>

                <section class="space-y-3" x-show="canSubmit || submitting" x-transition x-cloak>
                    <x-booking.step-label step="5" title="ملخص الموعد" />
                    <x-booking.summary />
                </section>

                <section class="space-y-3 sticky bottom-0 z-10 -mx-4 bg-gradient-to-t from-background via-background to-transparent px-4 pb-2 pt-4 sm:static sm:mx-0 sm:bg-none sm:px-0 sm:pb-0 sm:pt-0" x-show="selectedTime" x-cloak>
                    <x-booking.step-label step="6" title="إنشاء الموعد" class="hidden sm:flex" />
                    <p class="text-sm text-foreground-muted" x-show="! canSubmit && ! submitting" x-cloak>
                        أكمل البيانات المطلوبة لإنشاء الموعد
                    </p>
                    <button
                        type="submit"
                        class="inline-flex min-h-16 w-full items-center justify-center gap-3 rounded-2xl bg-primary px-8 text-lg font-semibold text-primary-foreground shadow-soft transition ds-press hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-muted focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                        x-bind:disabled="! canSubmit"
                    >
                        <template x-if="submitting">
                            <span class="inline-flex items-center gap-2">
                                <span class="ds-spinner" aria-hidden="true"></span>
                                جاري إنشاء الموعد…
                            </span>
                        </template>
                        <template x-if="! submitting">
                            <span>إنشاء الموعد</span>
                        </template>
                    </button>
                </section>
            </form>
        @endif
    </div>
@endsection
