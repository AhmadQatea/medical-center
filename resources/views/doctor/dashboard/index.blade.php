@extends('layouts.doctor')

@section('title', 'لوحة التحكم')

@section('content')
    @php
        $upcomingRows = $todayAppointments
            ->concat($upcomingAppointments)
            ->take(8);
    @endphp

    <x-layout.page-header
        title="لوحة التحكم"
        description="مرحباً، {{ auth()->user()->name }} — {{ $todayLabel }}"
    >
        <x-slot:actions>
            <x-ui.button href="{{ route('doctor.bookings.instant') }}" variant="primary" size="sm">
                + حجز جديد
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="ds-stack">
        <div class="ds-grid-stats">
            <x-ui.stat-card label="مواعيد اليوم" :value="(string) $stats['today_count']" variant="primary">
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat-card>
            <x-ui.stat-card label="مؤكدة" :value="(string) $stats['confirmed_count']" variant="success">
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat-card>
            <x-ui.stat-card label="قيد الانتظار" :value="(string) $stats['pending_count']" variant="warning">
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.9.693 2.166 1.638m-5.801 0A2.251 2.251 0 009 4.5V6h7.5V4.5" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat-card>
            <x-ui.stat-card label="قادمة" :value="(string) $stats['upcoming_count']" variant="accent">
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat-card>
        </div>

        @if (auth()->user()?->isAdmin() && isset($stats['clinics_count']))
            <div class="grid gap-3 sm:grid-cols-2">
                <x-ui.card class="!p-4">
                    <p class="text-xs text-foreground-muted">العيادات النشطة</p>
                    <p class="text-2xl font-bold text-foreground">{{ $stats['clinics_count'] }}</p>
                </x-ui.card>
                <x-ui.card class="!p-4">
                    <p class="text-xs text-foreground-muted">الأطباء النشطون</p>
                    <p class="text-2xl font-bold text-foreground">{{ $stats['active_doctors_count'] }}</p>
                </x-ui.card>
            </div>
        @endif

        <x-ui.section title="المواعيد القادمة" description="أقرب المواعيد المجدولة">
            <x-slot:actions>
                <x-ui.button href="{{ route('doctor.bookings.index') }}" variant="ghost" size="sm">عرض الكل ←</x-ui.button>
            </x-slot:actions>

            @if ($upcomingRows->isEmpty())
                <x-ui.empty-state
                    title="لا مواعيد قادمة"
                    description="ستظهر المواعيد هنا بعد إنشاء الحجوزات."
                    icon="calendar"
                />
            @else
                <x-doctor.appointments-table :appointments="$upcomingRows" :show-actions="false" />
            @endif
        </x-ui.section>

        <div class="ds-grid-2">
            <x-ui.section title="طلبات بانتظار المراجعة" description="مواعيد جديدة من صفحة الحجز">
                <x-slot:actions>
                    <x-ui.button href="{{ route('doctor.bookings.index', ['status' => 'pending']) }}" variant="ghost" size="sm">الكل</x-ui.button>
                </x-slot:actions>

                @if ($pendingAppointments->isEmpty())
                    <x-ui.empty-state
                        title="لا توجد طلبات معلقة"
                        description="عند حجز مريض جديد سيظهر هنا للمراجعة."
                        icon="clock"
                    />
                @else
                    <div class="ds-list-stack">
                        @foreach ($pendingAppointments as $appointment)
                            <x-doctor.pending-booking-card :appointment="$appointment" />
                        @endforeach
                    </div>
                @endif
            </x-ui.section>

            <x-ui.section title="مواعيد اليوم" description="حسب الوقت">
                <x-slot:actions>
                    <x-ui.button href="{{ route('doctor.timeline.index') }}" variant="ghost" size="sm">الخط الزمني</x-ui.button>
                </x-slot:actions>

                @if ($todayAppointments->isEmpty())
                    <x-ui.empty-state
                        title="لا توجد مواعيد اليوم"
                        description="ستظهر مواعيد اليوم هنا تلقائياً بعد إنشاء الحجوزات."
                    />
                @else
                    <div class="ds-list-stack">
                        @foreach ($todayAppointments as $appointment)
                            <a href="{{ route('doctor.bookings.show', $appointment) }}" class="block">
                                <x-doctor.appointment-row
                                    :time="$appointment->start_time"
                                    :patient="$appointment->patient?->name ?? '—'"
                                    :note="$appointment->typeLabel()"
                                    :phone="$appointment->patient?->phone"
                                    :status="$appointment->status->value"
                                />
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.section>
        </div>
    </div>
@endsection
