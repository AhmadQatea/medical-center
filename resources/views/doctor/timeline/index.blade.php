@extends('layouts.doctor')

@section('title', 'الخط الزمني')

@section('content')
    <x-layout.page-header
        title="الخط الزمني"
        description="{{ $todayLabel }} — مواعيد اليوم"
    />

    <div class="mx-auto max-w-2xl">
        @if ($entries->isEmpty())
            <x-ui.empty-state
                title="لا مواعيد في الخط الزمني"
                description="عندما تُحفظ المواعيد أو تتوفر أوقات في الجدول ستظهر هنا."
                icon="clock"
            >
                <x-ui.button href="{{ route('doctor.schedule.index') }}" variant="soft" size="sm">
                    إدارة الجدول
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="ds-list-stack">
                @foreach ($entries as $row)
                    <x-doctor.appointment-row
                        :time="$row['time_label'] ?? $row['time']"
                        :patient="$row['patient']"
                        :note="$row['note']"
                        :phone="$row['phone'] ?? null"
                        :status="$row['status']"
                        :current="$row['current'] ?? false"
                    >
                        @if (($row['status'] ?? '') === 'available')
                            <x-ui.button href="{{ route('doctor.bookings.instant') }}" variant="soft" size="sm" class="mt-2">
                                احجز هذا الوقت
                            </x-ui.button>
                        @endif
                    </x-doctor.appointment-row>
                @endforeach
            </div>
        @endif
    </div>
@endsection
