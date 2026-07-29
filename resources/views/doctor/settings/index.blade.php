@extends('layouts.doctor')

@section('title', 'إعدادات العيادة')

@section('content')
    <x-layout.page-header
        title="إعدادات العيادة"
        description="المعلومات الظاهرة في صفحة الحجز العامة"
    >
        <x-slot:actions>
            <x-ui.button href="{{ route('booking.index') }}" variant="secondary" size="sm" target="_blank" rel="noopener noreferrer">معاينة</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <form action="{{ route('doctor.settings.update') }}" method="post" class="ds-stack">
        @csrf
        @method('PUT')

        <x-ui.section title="الهوية">
            <x-ui.card>
                <div class="mb-6 flex items-center gap-4">
                    <x-ui.avatar
                        :name="$doctor->name"
                        :initials="\App\Support\Name::initials($doctor->name)"
                        size="xl"
                        :ring="true"
                    />
                    <x-ui.button type="button" variant="secondary" size="sm" disabled>رفع صورة</x-ui.button>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="clinic_name" label="اسم العيادة" :value="$settings->clinic_name" required />
                    <x-form.input name="doctor_name" label="اسم الطبيب" :value="$doctor->name" required />
                    <x-form.input name="specialty" label="التخصص" :value="$settings->specialty" required />
                    <x-form.input name="city" label="المدينة" :value="$settings->city" />
                    <div class="sm:col-span-2">
                        <x-form.textarea name="description" label="وصف مختصر" :value="$settings->description" :rows="3" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-form.input name="address" label="العنوان" :value="$settings->address" />
                    </div>
                </div>
            </x-ui.card>
        </x-ui.section>

        <x-ui.section title="واتساب">
            <x-ui.card>
                <x-form.input
                    name="whatsapp"
                    type="tel"
                    label="رقم واتساب"
                    :value="$settings->whatsapp_number"
                    dir="ltr"
                    required
                    help="مثال: +963959422413 أو 0959422413"
                />
            </x-ui.card>
        </x-ui.section>

        <x-ui.section title="أنواع المواعيد" description="إدارة أنواع الزيارات في صفحة الحجز">
            <x-ui.card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-base font-bold text-foreground">إدارة أنواع المواعيد</p>
                        <p class="text-sm text-foreground-muted">أضف أو عدّل أنواع المواعيد التي يختارها المرضى</p>
                    </div>
                    <x-ui.button href="{{ route('doctor.appointment-types.index') }}" variant="secondary" size="sm">
                        إدارة أنواع المواعيد
                    </x-ui.button>
                </div>
            </x-ui.card>
        </x-ui.section>

        <x-ui.button type="submit" variant="primary" size="xl" class="w-full sm:w-auto">حفظ التغييرات</x-ui.button>
    </form>
@endsection
