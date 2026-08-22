@extends('layouts.doctor')

@section('title', 'تعديل العيادة')

@section('content')
    <x-layout.page-header title="تعديل العيادة" :description="$clinic->name" />

    <x-ui.card>
        <form method="post" action="{{ route('doctor.clinics.update', $clinic) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('doctor.clinics._form', ['clinic' => $clinic])
            <x-ui.button type="submit" variant="primary">حفظ التعديلات</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-6">
        <div class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-foreground">حذف العيادة</h2>
                <p class="mt-1 text-sm text-foreground-muted">
                    @if ($canDelete)
                        سيتم حذف العيادة مع الإبقاء على المواعيد السابقة في السجل.
                    @elseif ($clinic->doctors_count > 0)
                        لا يمكن حذف العيادة لوجود {{ $clinic->doctors_count }} طبيب مرتبط بها.
                    @elseif ($hasFutureAppointments)
                        لا يمكن حذف العيادة لوجود مواعيد مستقبلية معلقة أو مؤكدة.
                    @else
                        لا يمكن حذف العيادة حالياً.
                    @endif
                </p>
            </div>

            @if ($canDelete)
                <form method="post" action="{{ route('doctor.clinics.destroy', $clinic) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه العيادة؟')">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger">حذف العيادة</x-ui.button>
                </form>
            @else
                <x-ui.button type="button" variant="danger" disabled>حذف العيادة</x-ui.button>
            @endif
        </div>
    </x-ui.card>
@endsection
