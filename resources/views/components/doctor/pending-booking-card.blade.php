{{-- Doctor: pending booking review card --}}
@props(['appointment'])

@php
    use App\Support\TimeFormat;
@endphp

<x-ui.card>
    <div class="space-y-4">
        <a href="{{ route('doctor.bookings.show', $appointment) }}" class="block space-y-1">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-lg font-bold text-foreground">{{ $appointment->patient?->name }}</p>
                    <p class="text-sm text-foreground-muted">
                        {{ $appointment->date?->locale('ar')->translatedFormat('l j F') }}
                        · {{ TimeFormat::arabic((string) $appointment->start_time) }}
                    </p>
                    @if ($appointment->appointmentType)
                        <x-ui.color-badge :name="$appointment->appointmentType->name" :color="$appointment->appointmentType->color" />
                    @endif
                    <p class="text-sm text-foreground-subtle" dir="ltr">+{{ ltrim((string) $appointment->patient?->phone, '+') }}</p>
                </div>
                <x-ui.badge variant="warning">قيد الانتظار</x-ui.badge>
            </div>
        </a>

        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
            <form method="post" action="{{ route('doctor.bookings.confirm', $appointment) }}">
                @csrf
                <x-ui.button type="submit" variant="primary" size="lg" class="w-full">
                    تأكيد
                </x-ui.button>
            </form>
            <x-ui.button href="{{ route('doctor.bookings.show', $appointment) }}" variant="soft" size="lg" class="w-full">
                عرض
            </x-ui.button>
            <form method="post" action="{{ route('doctor.bookings.destroy', $appointment) }}" onsubmit="return confirm('هل تريد إلغاء هذا الموعد؟');">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" size="lg" class="w-full">
                    إلغاء
                </x-ui.button>
            </form>
        </div>
    </div>
</x-ui.card>
