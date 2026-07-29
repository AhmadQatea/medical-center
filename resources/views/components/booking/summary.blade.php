{{-- Booking: confirmation summary --}}
@props([
    'dayModel' => 'summaryDay',
    'timeModel' => 'selectedTimeLabel',
    'nameModel' => 'patientName',
    'typeModel' => 'appointmentTypeLabel',
])

<div {{ $attributes->merge(['class' => 'space-y-3 rounded-2xl border border-border bg-surface-subtle px-5 py-4']) }}>
    <div class="flex items-center justify-between gap-3 text-base">
        <span class="text-foreground-muted">اليوم</span>
        <span class="font-bold text-foreground" x-text="{{ $dayModel }}"></span>
    </div>
    <div class="flex items-center justify-between gap-3 text-base">
        <span class="text-foreground-muted">الوقت</span>
        <span class="font-bold text-foreground" x-text="{{ $timeModel }}"></span>
    </div>
    <div class="flex items-center justify-between gap-3 text-base">
        <span class="text-foreground-muted">الاسم</span>
        <span class="font-bold text-foreground" x-text="{{ $nameModel }}"></span>
    </div>
    <div class="flex items-center justify-between gap-3 text-base">
        <span class="text-foreground-muted">نوع الموعد</span>
        <span class="font-bold text-foreground" x-text="{{ $typeModel }}"></span>
    </div>
</div>
