@extends('layouts.booking')

@section('title', 'لا يوجد أطباء متاحون')

@section('content')
    <div class="pb-10">
        <x-booking.empty-state
            title="لا يوجد أطباء متاحون حالياً في هذه العيادة"
            description="يرجى المحاولة لاحقاً أو اختيار عيادة أخرى."
        />

        <div class="mt-6 text-center">
            <a href="{{ route('booking.index') }}" class="text-sm font-medium text-primary hover:underline">← العودة لاختيار العيادة</a>
        </div>
    </div>
@endsection
