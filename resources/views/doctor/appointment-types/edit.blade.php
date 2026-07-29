@extends('layouts.doctor')

@section('title', 'تعديل نوع الموعد')

@section('content')
    <x-layout.page-header title="تعديل نوع الموعد" :description="$type->name" />

    <form method="post" action="{{ route('doctor.appointment-types.update', $type) }}" class="mx-auto max-w-xl space-y-5">
        @csrf
        @method('PUT')
        @include('doctor.appointment-types._form', ['type' => $type])

        <div class="flex flex-col gap-3 sm:flex-row">
            <x-ui.button type="submit" variant="primary" size="lg" class="flex-1">حفظ التغييرات</x-ui.button>
            <x-ui.button href="{{ route('doctor.appointment-types.index') }}" variant="ghost" size="lg" class="flex-1">إلغاء</x-ui.button>
        </div>
    </form>
@endsection
