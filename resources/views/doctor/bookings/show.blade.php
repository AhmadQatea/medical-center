@extends('layouts.doctor')

@section('title', 'تفاصيل الموعد')

@section('content')
    <x-layout.page-header
        title="تفاصيل الموعد"
        description="مراجعة الحجز واتخاذ الإجراء المناسب"
    >
        <x-slot:actions>
            <x-ui.button href="{{ route('doctor.bookings.index') }}" variant="ghost" size="sm">
                العودة
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mx-auto max-w-xl space-y-5">
        <x-ui.section title="معلومات المريض">
            <x-ui.card>
                <dl class="space-y-0">
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">الاسم الكامل</dt>
                        <dd class="ds-detail-value !font-bold">{{ $appointment->patient?->name ?? '—' }}</dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">رقم واتساب</dt>
                        <dd class="ds-detail-value" dir="ltr">
                            +{{ ltrim((string) $appointment->patient?->phone, '+') }}
                        </dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">نوع الموعد</dt>
                        <dd class="text-end">
                            @if ($appointment->appointmentType)
                                <x-ui.color-badge
                                    :name="$appointment->appointmentType->name"
                                    :color="$appointment->appointmentType->color"
                                />
                            @else
                                <span class="ds-detail-value">—</span>
                            @endif
                        </dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">حالة الحجز</dt>
                        <dd>
                            <x-ui.badge :variant="$appointment->status->color()" size="md">
                                {{ $appointment->status->label() }}
                            </x-ui.badge>
                        </dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">تاريخ الإنشاء</dt>
                        <dd class="ds-detail-value">
                            {{ $appointment->created_at?->locale('ar')->translatedFormat('j F Y — h:i A') }}
                        </dd>
                    </div>
                </dl>
            </x-ui.card>
        </x-ui.section>

        <x-ui.section title="معلومات الموعد">
            <x-ui.card>
                <dl class="space-y-0">
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">اليوم</dt>
                        <dd class="ds-detail-value !font-bold">
                            {{ $appointment->date?->locale('ar')->translatedFormat('l') }}
                        </dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">التاريخ</dt>
                        <dd class="ds-detail-value !font-bold">
                            {{ $appointment->date?->locale('ar')->translatedFormat('j F Y') }}
                        </dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">الوقت</dt>
                        <dd class="ds-detail-value !font-bold">@arabicTime($appointment->start_time)</dd>
                    </div>
                </dl>
            </x-ui.card>
        </x-ui.section>

        @if ($whatsappUrl)
            <x-ui.section title="إرسال التأكيد" description="افتح واتساب برسالة جاهزة للمريض">
                <x-doctor.whatsapp-confirm-button :href="$whatsappUrl" />

                @if ($confirmationMessage)
                    <x-ui.card class="mt-3 !bg-surface-subtle">
                        <p class="mb-2 text-xs font-semibold text-foreground-muted">معاينة الرسالة</p>
                        <pre class="whitespace-pre-wrap text-sm leading-relaxed text-foreground">{{ $confirmationMessage }}</pre>
                    </x-ui.card>
                @endif
            </x-ui.section>
        @endif

        <x-ui.section title="الإجراءات">
            <x-doctor.booking-actions :appointment="$appointment" />
        </x-ui.section>
    </div>
@endsection
