@extends('layouts.doctor')

@section('title', 'قائمة الحجوزات')

@section('content')
    @php
        $contextQuery = app(\App\Services\AdminBookingContextService::class)->queryParams(
            $bookingContext['clinic'] ?? null,
            $bookingContext['doctor'] ?? null,
        );
        $canCreateInstant = ($bookingContext['clinic'] ?? null) !== null
            && ($bookingContext['doctor'] ?? null) !== null;
    @endphp

    <x-layout.page-header
        title="قائمة الحجوزات"
        description="عرض كل الحجوزات مع التصفية حسب العيادة ثم الطبيب"
    >
        <x-slot:actions>
            <x-ui.button
                href="{{ route('doctor.bookings.instant', $contextQuery) }}"
                variant="primary"
                size="sm"
            >
                + حجز جديد
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="ds-stack">
        <x-doctor.clinic-doctor-context
            :context="$bookingContext"
            :action="route('doctor.bookings.index')"
            :preserve="request()->only(['search', 'status', 'date'])"
            :blocking="false"
        />

        <x-ui.card class="!p-4 sm:!p-5">
            <form method="get" action="{{ route('doctor.bookings.index') }}" class="grid gap-4 lg:grid-cols-[2fr_1fr_1fr_auto]">
                @if ($bookingContext['clinic'] ?? null)
                    <input type="hidden" name="clinic_id" value="{{ $bookingContext['clinic']->id }}">
                @endif
                @if ($bookingContext['doctor'] ?? null)
                    <input type="hidden" name="doctor_id" value="{{ $bookingContext['doctor']->id }}">
                @endif

                <x-form.input
                    name="search"
                    label="بحث"
                    placeholder="ابحث باسم المريض أو رقم الهاتف..."
                    :value="request('search')"
                />
                <x-form.select name="status" label="حالة الحجز" :value="request('status')">
                    <option value="">الكل</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input name="date" type="date" label="التاريخ" :value="request('date')" dir="ltr" />
                <div class="flex items-end gap-2">
                    <x-ui.button type="submit" variant="soft" size="md" class="w-full sm:w-auto">تصفية</x-ui.button>
                    @if (request()->hasAny(['search', 'status', 'date', 'clinic_id', 'doctor_id']))
                        <a
                            href="{{ route('doctor.bookings.index') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl px-4 text-sm font-medium text-foreground-muted hover:bg-surface-subtle"
                        >
                            مسح
                        </a>
                    @endif
                </div>
            </form>
        </x-ui.card>

        @if ($appointments->isEmpty())
            <x-ui.empty-state
                title="لا توجد مواعيد"
                description="{{ request()->hasAny(['search', 'status', 'date', 'clinic_id', 'doctor_id']) ? 'لا نتائج مطابقة للتصفية الحالية.' : 'ستظهر الحجوزات هنا بعد إنشائها من صفحة الحجز أو الحجز الفوري.' }}"
            >
                @if (request()->hasAny(['search', 'status', 'date', 'clinic_id', 'doctor_id']))
                    <x-ui.button href="{{ route('doctor.bookings.index') }}" variant="soft" size="sm">
                        عرض كل المواعيد
                    </x-ui.button>
                @elseif ($canCreateInstant)
                    <x-ui.button href="{{ route('doctor.bookings.instant', $contextQuery) }}" variant="soft" size="sm">
                        إنشاء موعد
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-foreground-muted">
                    عرض <span class="font-semibold text-foreground">{{ $appointments->firstItem() }}</span>
                    إلى <span class="font-semibold text-foreground">{{ $appointments->lastItem() }}</span>
                    من أصل <span class="font-semibold text-foreground">{{ $appointments->total() }}</span> حجز
                </p>
            </div>

            <div class="hidden md:block">
                <x-doctor.appointments-table
                    :appointments="$appointments"
                    :whatsapp-urls="$whatsappUrls"
                />
            </div>

            <div class="ds-list-stack md:hidden">
                @foreach ($appointments as $appointment)
                    <x-ui.card>
                        <div class="flex flex-col gap-4">
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-lg font-bold text-foreground">{{ $appointment->patient?->name }}</p>
                                    <x-ui.badge :variant="$appointment->status->color()" size="sm">
                                        {{ $appointment->status->label() }}
                                    </x-ui.badge>
                                </div>
                                @if ($appointment->clinic || $appointment->user)
                                    <p class="text-xs text-foreground-muted">
                                        {{ $appointment->clinic?->name ?? '—' }}
                                        · {{ $appointment->user?->name ?? '—' }}
                                    </p>
                                @endif
                                <p class="text-sm text-foreground-muted">
                                    {{ $appointment->date?->locale('ar')->translatedFormat('l j F Y') }}
                                    · @arabicTime($appointment->start_time)
                                </p>
                                @if ($appointment->appointmentType)
                                    <x-ui.color-badge
                                        :name="$appointment->appointmentType->name"
                                        :color="$appointment->appointmentType->color"
                                    />
                                @endif
                                <p class="text-sm text-foreground-subtle" dir="ltr">
                                    +{{ ltrim((string) $appointment->patient?->phone, '+') }}
                                </p>
                            </div>

                            <x-doctor.booking-row-actions
                                :appointment="$appointment"
                                :whatsapp-url="$whatsappUrls[$appointment->id] ?? null"
                            />
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            {{ $appointments->withQueryString()->links() }}
        @endif
    </div>
@endsection
