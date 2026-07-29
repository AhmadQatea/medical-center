@extends('layouts.doctor')

@section('title', 'المرضى')

@section('content')
    <x-layout.page-header title="المرضى" description="سجل مرضى العيادة" />

    <div class="ds-stack">
        <x-ui.card>
            <form method="get" action="{{ route('doctor.patients.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <x-form.input
                        name="search"
                        label="بحث"
                        placeholder="اسم المريض أو رقم الجوال"
                        :value="request('search')"
                        autocomplete="off"
                    />
                </div>
                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="soft" size="md" class="flex-1 sm:flex-none">بحث</x-ui.button>
                    @if (request()->filled('search'))
                        <x-ui.button href="{{ route('doctor.patients.index') }}" variant="ghost" size="md">
                            مسح
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        @if ($patients->isEmpty())
            <x-ui.empty-state
                title="{{ request()->filled('search') ? 'لا نتائج للبحث' : 'لا يوجد مرضى بعد' }}"
                description="{{ request()->filled('search') ? 'جرّب اسماً أو رقماً مختلفاً.' : 'سيُملأ سجل المرضى تلقائياً عند إنشاء الحجوزات.' }}"
                icon="users"
            >
                @if (request()->filled('search'))
                    <x-ui.button href="{{ route('doctor.patients.index') }}" variant="soft" size="sm">
                        عرض كل المرضى
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            <p class="text-sm text-foreground-muted">
                عدد المرضى: <span class="font-bold text-foreground">{{ $patients->total() }}</span>
            </p>
            <div class="ds-list-stack">
                @foreach ($patients as $patient)
                    <x-doctor.patient-row
                        :name="$patient->name"
                        :phone="'+'.$patient->phone"
                        :last-visit="$patient->updated_at?->locale('ar')->translatedFormat('j F Y') ?? '—'"
                        :visits="$patient->appointments_count"
                        :notes="null"
                    />
                @endforeach
            </div>
            {{ $patients->withQueryString()->links() }}
        @endif
    </div>
@endsection
