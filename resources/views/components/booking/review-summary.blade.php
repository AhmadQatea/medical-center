{{-- Booking: review summary for confirm step --}}
@props([
    'clinic' => null,
    'doctor' => null,
    'dayModel' => 'summaryDay',
    'timeModel' => 'selectedTimeLabel',
    'nameModel' => 'patientName',
    'typeModel' => 'appointmentTypeLabel',
])

<div {{ $attributes->merge(['class' => 'bk-review-card']) }}>
    @if ($clinic)
        <div class="bk-review-row">
            <span class="bk-review-label">العيادة</span>
            <span class="bk-review-value">{{ $clinic }}</span>
        </div>
    @endif

    @if ($doctor)
        <div class="bk-review-row">
            <span class="bk-review-label">الطبيب</span>
            <span class="bk-review-value">{{ $doctor }}</span>
        </div>
    @endif

    <div class="bk-review-row">
        <span class="bk-review-label">نوع الموعد</span>
        <span class="bk-review-value" x-text="{{ $typeModel }}"></span>
    </div>
    <div class="bk-review-row">
        <span class="bk-review-label">اليوم</span>
        <span class="bk-review-value" x-text="{{ $dayModel }}"></span>
    </div>
    <div class="bk-review-row">
        <span class="bk-review-label">الوقت</span>
        <span class="bk-review-value" x-text="{{ $timeModel }}"></span>
    </div>
    <div class="bk-review-row">
        <span class="bk-review-label">الاسم</span>
        <span class="bk-review-value" x-text="{{ $nameModel }}"></span>
    </div>
    <div class="bk-review-row">
        <span class="bk-review-label">واتساب</span>
        <span class="bk-review-value" dir="ltr" x-text="normalizedPhone"></span>
    </div>
</div>
