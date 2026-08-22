{{-- Doctor: Appointments table — CarePoint list style --}}
@props([
    'appointments',
    'whatsappUrls' => [],
    'showActions' => true,
])

<div class="ds-table-wrap">
    <table class="ds-table">
        <thead>
            <tr>
                <th>المريض / الهاتف</th>
                <th>العيادة / الخدمة</th>
                <th>الطبيب</th>
                <th>التاريخ / الوقت</th>
                <th>الحالة</th>
                @if ($showActions)
                    <th>إجراءات</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($appointments as $appointment)
                <tr>
                    <td>
                        <a href="{{ route('doctor.bookings.show', $appointment) }}" class="group block min-w-[10rem]">
                            <p class="font-semibold text-foreground group-hover:text-primary">{{ $appointment->patient?->name ?? '—' }}</p>
                            <p class="mt-0.5 text-xs text-foreground-muted" dir="ltr">
                                {{ $appointment->patient?->phone ? '+'.ltrim((string) $appointment->patient->phone, '+') : '—' }}
                            </p>
                        </a>
                    </td>
                    <td>
                        <p class="font-medium text-foreground">{{ $appointment->clinic?->name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs text-foreground-muted">
                            {{ $appointment->appointmentType?->name ?? $appointment->typeLabel() }}
                        </p>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-ui.avatar :name="$appointment->user?->name ?? '?'" size="xs" />
                            <span class="text-sm font-medium">{{ $appointment->user?->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <p class="font-medium">{{ $appointment->date?->locale('ar')->translatedFormat('j F Y') }}</p>
                        <p class="mt-0.5 flex items-center gap-1 text-xs text-foreground-muted">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @arabicTime($appointment->start_time)
                        </p>
                    </td>
                    <td>
                        <x-ui.badge :variant="$appointment->status->color()" size="sm">
                            {{ $appointment->status->label() }}
                        </x-ui.badge>
                    </td>
                    @if ($showActions)
                        <td>
                            <x-doctor.booking-row-actions
                                :appointment="$appointment"
                                :whatsapp-url="$whatsappUrls[$appointment->id] ?? null"
                            />
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showActions ? 6 : 5 }}" class="!py-10 text-center text-foreground-muted">
                        {{ $slot->isEmpty() ? 'لا توجد مواعيد' : $slot }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
