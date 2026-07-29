@extends('layouts.doctor')

@section('title', 'إعادة جدولة الموعد')

@section('content')
    <x-layout.page-header
        title="إعادة جدولة الموعد"
        description="{{ $appointment->patient?->name }} — {{ $appointment->typeLabel() }}"
    />

    <form method="post" action="{{ route('doctor.bookings.update', $appointment) }}" class="mx-auto max-w-xl space-y-5">
        @csrf
        @method('PUT')

        <x-ui.card>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input
                    name="date"
                    type="date"
                    label="التاريخ"
                    :value="old('date', $appointment->date?->toDateString())"
                    required
                    dir="ltr"
                    class="!min-h-14"
                />
                <x-form.select name="start_time" label="الوقت" required>
                    <option value="">اختر الوقت</option>
                    @foreach ($slots as $slot)
                        <option value="{{ $slot }}" @selected(old('start_time', \App\Support\TimeFormat::normalize((string) $appointment->start_time)) === $slot)>
                            @arabicTime($slot)
                        </option>
                    @endforeach
                </x-form.select>
            </div>
        </x-ui.card>

        <div class="flex flex-col gap-3 sm:flex-row">
            <x-ui.button type="submit" variant="primary" size="lg" class="flex-1">حفظ التغييرات</x-ui.button>
            <x-ui.button href="{{ route('doctor.bookings.show', $appointment) }}" variant="ghost" size="lg" class="flex-1">إلغاء</x-ui.button>
        </div>
    </form>
@endsection
