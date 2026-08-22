@extends('layouts.doctor')

@section('title', 'طبيب جديد')

@section('content')
    <x-layout.page-header title="طبيب جديد" />

    <x-ui.card>
        <form method="post" action="{{ route('doctor.doctors.store') }}" class="space-y-6">
            @csrf
            @include('doctor.doctors._form', ['clinics' => $clinics])
            <x-ui.button type="submit" variant="primary">حفظ</x-ui.button>
        </form>
    </x-ui.card>
@endsection
