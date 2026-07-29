@extends('layouts.doctor')

@section('title', 'نوع موعد جديد')

@section('content')
    <x-layout.page-header title="نوع موعد جديد" description="يظهر للمرضى في صفحة الحجز عند التفعيل" />

    <form method="post" action="{{ route('doctor.appointment-types.store') }}" class="mx-auto max-w-xl space-y-5">
        @csrf
        @include('doctor.appointment-types._form')

        <div class="flex flex-col gap-3 sm:flex-row">
            <x-ui.button type="submit" variant="primary" size="lg" class="flex-1">حفظ</x-ui.button>
            <x-ui.button href="{{ route('doctor.appointment-types.index') }}" variant="ghost" size="lg" class="flex-1">إلغاء</x-ui.button>
        </div>
    </form>
@endsection
