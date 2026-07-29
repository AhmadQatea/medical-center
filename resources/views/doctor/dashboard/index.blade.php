@extends('layouts.doctor')

@section('title', 'لوحة التحكم')

@section('content')
    <x-layout.page-header
        title="مرحباً، {{ auth()->user()->name }}"
        description="{{ $todayLabel }}"
    />

    <div class="ds-stack">
        <div class="ds-grid-stats">
            <x-ui.stat-card label="بانتظار التأكيد" :value="(string) $stats['pending_count']" hint="طلبات جديدة" variant="warning" />
            <x-ui.stat-card label="مؤكدة قادمة" :value="(string) $stats['confirmed_count']" hint="مواعيد مؤكدة" variant="success" />
            <x-ui.stat-card label="مواعيد اليوم" :value="(string) $stats['today_count']" hint="من قاعدة البيانات" variant="primary" />
            <x-ui.stat-card label="قادمة" :value="(string) $stats['upcoming_count']" hint="بعد اليوم" variant="accent" />
        </div>

        <x-ui.card class="!border-primary/20 !bg-primary-soft">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <p class="text-lg font-bold text-primary">حجز فوري</p>
                    <p class="text-sm text-foreground-muted">موعد سريع لمريض اتصل أو حضر للعيادة</p>
                </div>
                <x-ui.button href="{{ route('doctor.bookings.instant') }}" variant="primary" size="lg" class="w-full sm:w-auto">
                    إنشاء موعد
                </x-ui.button>
            </div>
        </x-ui.card>

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

            <x-ui.section title="المواعيد المؤكدة" description="جاهزة للإرسال أو الحضور">
                <x-slot:actions>
                    <x-ui.button href="{{ route('doctor.bookings.index', ['status' => 'confirmed']) }}" variant="ghost" size="sm">الكل</x-ui.button>
                </x-slot:actions>

                @if ($confirmedAppointments->isEmpty())
                    <x-ui.empty-state
                        title="لا مواعيد مؤكدة"
                        description="بعد تأكيد الحجز ستظهر المواعيد هنا."
                    />
                @else
                    <div class="ds-list-stack">
                        @foreach ($confirmedAppointments as $appointment)
                            <x-ui.card>
                                <a href="{{ route('doctor.bookings.show', $appointment) }}" class="block space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-base font-bold text-foreground">{{ $appointment->patient?->name }}</p>
                                        <x-ui.badge variant="success" size="sm">مؤكد</x-ui.badge>
                                    </div>
                                    <p class="text-sm text-foreground-muted">
                                        {{ $appointment->date?->locale('ar')->translatedFormat('l j F') }}
                                        · @arabicTime($appointment->start_time)
                                    </p>
                                    @if ($appointment->appointmentType)
                                        <x-ui.color-badge
                                            :name="$appointment->appointmentType->name"
                                            :color="$appointment->appointmentType->color"
                                        />
                                    @endif
                                </a>
                            </x-ui.card>
                        @endforeach
                    </div>
                @endif
            </x-ui.section>
        </div>

        <div class="ds-grid-2">
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

            <x-ui.section title="المواعيد القادمة" description="بعد اليوم">
                <x-slot:actions>
                    <x-ui.button href="{{ route('doctor.bookings.index') }}" variant="ghost" size="sm">الكل</x-ui.button>
                </x-slot:actions>

                @if ($upcomingAppointments->isEmpty())
                    <x-ui.empty-state
                        title="لا مواعيد قادمة"
                        description="الحجوزات المستقبلية ستظهر هنا."
                    />
                @else
                    <div class="ds-list-stack">
                        @foreach ($upcomingAppointments as $appointment)
                            <x-ui.card>
                                <a href="{{ route('doctor.bookings.show', $appointment) }}" class="block space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-base font-bold text-foreground">{{ $appointment->patient?->name }}</p>
                                        <x-ui.badge :variant="$appointment->status->color()" size="sm">
                                            {{ $appointment->status->label() }}
                                        </x-ui.badge>
                                    </div>
                                    <p class="text-sm text-foreground-muted">
                                        {{ $appointment->date?->locale('ar')->translatedFormat('l j F') }}
                                        · @arabicTime($appointment->start_time)
                                    </p>
                                </a>
                            </x-ui.card>
                        @endforeach
                    </div>
                @endif
            </x-ui.section>
        </div>
    </div>
@endsection
