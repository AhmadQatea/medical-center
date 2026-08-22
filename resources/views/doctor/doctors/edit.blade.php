@extends('layouts.doctor')

@section('title', 'تعديل الطبيب')

@section('content')
    <x-layout.page-header title="تعديل الطبيب" :description="$doctor->name" />

    <x-ui.card>
        <form method="post" action="{{ route('doctor.doctors.update', $doctor) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('doctor.doctors._form', ['doctor' => $doctor, 'clinics' => $clinics])
            <x-ui.button type="submit" variant="primary">حفظ التعديلات</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-6">
        <div class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-foreground">حذف الطبيب</h2>
                <p class="mt-1 text-sm text-foreground-muted">
                    @if ($canDelete)
                        لا يمكن التراجع عن هذا الإجراء. متاح فقط للأطباء بدون مواعيد مسجلة.
                    @else
                        لا يمكن حذف الطبيب لوجود {{ $doctor->appointments_count }} موعد مرتبط به.
                    @endif
                </p>
            </div>

            @if ($canDelete)
                <form method="post" action="{{ route('doctor.doctors.destroy', $doctor) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطبيب؟')">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger">حذف الطبيب</x-ui.button>
                </form>
            @endif
        </div>
    </x-ui.card>
@endsection
