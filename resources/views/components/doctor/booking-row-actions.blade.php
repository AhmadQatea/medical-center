{{-- Doctor: booking list actions — always visible, wrap on small screens --}}
@props([
    'appointment',
    'whatsappUrl' => null,
])

@php
    $canEdit = $appointment->status->isEditable();
    $canWhatsApp = $appointment->status->canSendWhatsApp() && filled($whatsappUrl);
    $canCancel = $appointment->status->canCancel();
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
    <x-ui.button href="{{ route('doctor.bookings.show', $appointment) }}" variant="soft" size="sm">عرض</x-ui.button>

    @if ($canEdit)
        <x-ui.button href="{{ route('doctor.bookings.edit', $appointment) }}" variant="ghost" size="sm">تعديل</x-ui.button>
    @endif

    @if ($canWhatsApp)
        <a
            href="{{ $whatsappUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl bg-[#25D366] px-4 text-sm font-medium text-white shadow-soft transition hover:brightness-95"
        >
            واتساب
        </a>
    @endif

    @if ($canCancel)
        <form method="post" action="{{ route('doctor.bookings.destroy', $appointment) }}" onsubmit="return confirm('إلغاء هذا الموعد؟');">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" variant="danger" size="sm">حذف</x-ui.button>
        </form>
    @endif
</div>
