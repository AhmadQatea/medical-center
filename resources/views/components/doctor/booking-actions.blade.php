{{-- Doctor: status-aware booking action buttons — clear hierarchy --}}
@props(['appointment'])

@php
    use App\Enums\AppointmentStatus;

    $status = $appointment->status;
@endphp

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if ($status->canConfirm())
        <form method="post" action="{{ route('doctor.bookings.confirm', $appointment) }}">
            @csrf
            <x-ui.button type="submit" variant="primary" size="xl" class="w-full">
                تأكيد الحجز
            </x-ui.button>
        </form>
    @endif

    @if ($status->canComplete())
        <form method="post" action="{{ route('doctor.bookings.status', $appointment) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ AppointmentStatus::Completed->value }}">
            <x-ui.button type="submit" variant="soft" size="lg" class="w-full">
                إكمال الموعد
            </x-ui.button>
        </form>
    @endif

    @if ($status->isEditable())
        <x-ui.button
            href="{{ route('doctor.bookings.edit', $appointment) }}"
            variant="secondary"
            size="lg"
            class="w-full"
        >
            {{ $status === AppointmentStatus::Confirmed ? 'إعادة جدولة' : 'تعديل الموعد' }}
        </x-ui.button>
    @endif

    @if ($status->canMarkNoShow())
        <form method="post" action="{{ route('doctor.bookings.status', $appointment) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ AppointmentStatus::NoShow->value }}">
            <x-ui.button type="submit" variant="ghost" size="lg" class="w-full">
                تسجيل عدم حضور
            </x-ui.button>
        </form>
    @endif

    @if ($status->canCancel())
        <form
            method="post"
            action="{{ route('doctor.bookings.status', $appointment) }}"
            onsubmit="return confirm('هل تريد إلغاء هذا الموعد؟');"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ AppointmentStatus::Cancelled->value }}">
            <x-ui.button type="submit" variant="danger" size="lg" class="w-full">
                إلغاء الحجز
            </x-ui.button>
        </form>
    @endif

    <x-ui.button href="{{ route('doctor.bookings.index') }}" variant="ghost" size="md" class="w-full !text-foreground-subtle">
        العودة للمواعيد
    </x-ui.button>
</div>
