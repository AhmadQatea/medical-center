@extends('layouts.doctor')

@section('title', 'عيادة جديدة')

@section('content')
    <x-layout.page-header title="عيادة جديدة" />

    <x-ui.card>
        <form method="post" action="{{ route('doctor.clinics.store') }}" class="space-y-6">
            @csrf
            @include('doctor.clinics._form')
            <x-ui.button type="submit" variant="primary">حفظ</x-ui.button>
        </form>
    </x-ui.card>
@endsection
