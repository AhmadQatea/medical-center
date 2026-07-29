@extends('layouts.booking')

@section('title', 'تم الحجز')

@section('content')
    <div class="space-y-6 py-6 text-center" role="status">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-success-soft text-success shadow-soft" aria-hidden="true">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>

        <div class="space-y-3">
            <h1 class="text-2xl font-bold text-foreground">تم إرسال طلب الحجز بنجاح</h1>
            <p class="mx-auto max-w-md text-base leading-relaxed text-foreground-muted">
                سيتم مراجعة طلبك من قبل الطبيب.
                <br>
                ستصلك رسالة تأكيد عبر واتساب بعد اعتماد الموعد.
            </p>
            <div class="ds-gold-line mx-auto mt-3" aria-hidden="true"></div>
        </div>

        <x-ui.card class="text-start !shadow-soft-md">
            <dl class="space-y-0">
                <div class="ds-detail-row">
                    <dt class="ds-detail-label">العيادة</dt>
                    <dd class="ds-detail-value">{{ $settings?->clinic_name ?? config('clinic.name') }}</dd>
                </div>
                <div class="ds-detail-row">
                    <dt class="ds-detail-label">الطبيب</dt>
                    <dd class="ds-detail-value">{{ $doctor?->name ?? config('clinic.doctor.name') }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.button href="{{ route('booking.index') }}" variant="primary" size="xl" class="w-full">
            العودة للحجز
        </x-ui.button>
    </div>
@endsection
