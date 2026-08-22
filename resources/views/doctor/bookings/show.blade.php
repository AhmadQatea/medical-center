@extends('layouts.doctor')

@section('title', 'تفاصيل الحجز')

@section('content')
    @php
        $contextQuery = app(\App\Services\AdminBookingContextService::class)->queryParams(
            $appointment->clinic,
            $appointment->user,
        );
    @endphp

    <x-layout.page-header
        title="تفاصيل الحجز"
        description="#{{ str_pad((string) $appointment->id, 4, '0', STR_PAD_LEFT) }}"
    >
        <x-slot:actions>
            @if ($appointment->status->isEditable())
                <x-ui.button href="{{ route('doctor.bookings.edit', $appointment) }}" variant="secondary" size="sm">
                    تعديل
                </x-ui.button>
            @endif
            <x-ui.button href="{{ route('doctor.bookings.index', $contextQuery) }}" variant="ghost" size="sm">
                العودة
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        <div class="ds-stack">
            <div class="ds-section-card">
                <div class="ds-section-card-header">
                    <span class="ds-section-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </span>
                    <span>معلومات المريض</span>
                </div>

                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                    <x-ui.avatar :name="$appointment->patient?->name" size="lg" />
                    <dl class="min-w-0 flex-1 space-y-0">
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
                    </dl>
                </div>

                @if ($whatsappUrl)
                    <div class="mt-5 flex flex-wrap gap-2 border-t border-border pt-5">
                        <x-doctor.whatsapp-confirm-button :href="$whatsappUrl" />
                    </div>
                @endif
            </div>

            <div class="ds-section-card">
                <div class="ds-section-card-header">
                    <span class="ds-section-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </span>
                    <span>تفاصيل الزيارة</span>
                </div>

                <div class="mb-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-surface-subtle p-4">
                        <p class="text-xs text-foreground-muted">التاريخ</p>
                        <p class="mt-1 font-bold text-foreground">
                            {{ $appointment->date?->locale('ar')->translatedFormat('l، j F Y') }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-surface-subtle p-4">
                        <p class="text-xs text-foreground-muted">الوقت</p>
                        <p class="mt-1 font-bold text-foreground">@arabicTime($appointment->start_time)</p>
                    </div>
                </div>

                <dl class="space-y-0">
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">العيادة</dt>
                        <dd class="ds-detail-value">{{ $appointment->clinic?->name ?? '—' }}</dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">الطبيب المعالج</dt>
                        <dd class="ds-detail-value">{{ $appointment->user?->name ?? '—' }}</dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">مصدر الحجز</dt>
                        <dd class="ds-detail-value">{{ $appointment->source->label() }}</dd>
                    </div>
                    <div class="ds-detail-row">
                        <dt class="ds-detail-label">تاريخ الإنشاء</dt>
                        <dd class="ds-detail-value">
                            {{ $appointment->created_at?->locale('ar')->translatedFormat('j F Y — h:i A') }}
                        </dd>
                    </div>
                </dl>
            </div>

            @if ($whatsappUrl && $confirmationMessage)
                <x-ui.card class="!bg-surface-subtle">
                    <p class="mb-2 text-xs font-semibold text-foreground-muted">معاينة رسالة واتساب</p>
                    <pre class="whitespace-pre-wrap text-sm leading-relaxed text-foreground">{{ $confirmationMessage }}</pre>
                </x-ui.card>
            @endif
        </div>

        <div class="ds-stack">
            <div class="ds-section-card">
                <div class="ds-section-card-header">
                    <span class="ds-section-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span>حالة الحجز</span>
                </div>

                <div class="space-y-4">
                    <x-ui.badge :variant="$appointment->status->color()" size="md" class="!px-4 !py-2 !text-sm">
                        {{ $appointment->status->label() }}
                    </x-ui.badge>

                    <x-doctor.booking-actions :appointment="$appointment" />
                </div>
            </div>

            @if ($whatsappUrl)
                <div class="ds-section-card">
                    <div class="ds-section-card-header">
                        <span class="ds-section-card-icon">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            </svg>
                        </span>
                        <span>التواصل</span>
                    </div>
                    <p class="mb-4 text-sm text-foreground-muted">إرسال رسائل تأكيد للمريض عبر واتساب</p>
                    <x-doctor.whatsapp-confirm-button :href="$whatsappUrl" class="w-full" />
                </div>
            @endif
        </div>
    </div>
@endsection
