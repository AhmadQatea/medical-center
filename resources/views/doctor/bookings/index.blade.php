@extends('layouts.doctor')

@section('title', 'المواعيد')

@section('content')
    <x-layout.page-header title="المواعيد" description="كل الحجوزات في مكان واحد">
        <x-slot:actions>
            <x-ui.button href="{{ route('doctor.bookings.instant') }}" variant="primary" size="sm">موعد جديد</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="ds-stack">
        <x-ui.card>
            <form method="get" action="{{ route('doctor.bookings.index') }}" class="grid gap-4 sm:grid-cols-[1fr_1fr_auto]">
                <x-form.input
                    name="search"
                    label="بحث"
                    placeholder="اسم المريض أو الجوال"
                    :value="request('search')"
                />
                <x-form.select name="status" label="الحالة" :value="request('status')">
                    <option value="">الكل</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="inline-flex min-h-14 w-full items-center justify-center rounded-xl bg-primary-soft px-5 text-sm font-semibold text-primary transition hover:bg-primary-muted/40 sm:w-auto"
                    >
                        تصفية
                    </button>
                    @if (request()->filled('search') || request()->filled('status'))
                        <a
                            href="{{ route('doctor.bookings.index') }}"
                            class="inline-flex min-h-14 items-center justify-center rounded-xl px-4 text-sm font-medium text-foreground-muted hover:bg-surface-subtle"
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
                description="{{ request()->hasAny(['search', 'status']) ? 'لا نتائج مطابقة للتصفية الحالية.' : 'ستظهر الحجوزات هنا بعد إنشائها من صفحة الحجز أو الحجز الفوري.' }}"
            >
                @if (request()->hasAny(['search', 'status']))
                    <x-ui.button href="{{ route('doctor.bookings.index') }}" variant="soft" size="sm">
                        عرض كل المواعيد
                    </x-ui.button>
                @else
                    <x-ui.button href="{{ route('doctor.bookings.instant') }}" variant="soft" size="sm">
                        إنشاء موعد
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            <p class="text-sm text-foreground-muted">
                عدد النتائج: <span class="font-bold text-foreground">{{ $appointments->total() }}</span>
            </p>

            <div class="ds-list-stack">
                @foreach ($appointments as $appointment)
                    <x-ui.card>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-lg font-bold text-foreground">{{ $appointment->patient?->name }}</p>
                                    <x-ui.badge :variant="$appointment->status->color()" size="sm">
                                        {{ $appointment->status->label() }}
                                    </x-ui.badge>
                                </div>
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

                            <div class="shrink-0">
                                <x-doctor.booking-row-actions
                                    :appointment="$appointment"
                                    :whatsapp-url="$whatsappUrls[$appointment->id] ?? null"
                                />
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            {{ $appointments->withQueryString()->links() }}
        @endif
    </div>
@endsection
