@extends('layouts.booking')

@section('title', 'الحجز غير متاح')

@section('content')
    <div class="pb-10">
        <x-booking.empty-state
            title="لا توجد عيادات متاحة للحجز حالياً"
            description="يرجى المحاولة لاحقاً أو التواصل مع {{ $medicalCenterName }}."
        />
    </div>
@endsection
