@props(['doctor' => null, 'clinics'])

<div class="grid gap-4">
    <x-form.select name="clinic_id" label="العيادة" required>
        <option value="">اختر العيادة</option>
        @foreach ($clinics as $clinic)
            <option value="{{ $clinic->id }}" @selected((int) old('clinic_id', $doctor?->clinic_id) === $clinic->id)>{{ $clinic->name }}</option>
        @endforeach
    </x-form.select>
    <x-form.input name="name" label="اسم الطبيب" :value="old('name', $doctor?->name)" required />
    <x-form.input
        name="phone"
        type="tel"
        label="رقم الهاتف"
        :value="old('phone', $doctor?->phone)"
        required
        dir="ltr"
        placeholder="09XXXXXXXX"
        help="للتواصل فقط — لا يُستخدم للدخول أو الحجز"
    />
    <x-form.input name="specialty" label="التخصص" :value="old('specialty', $doctor?->specialty)" />
    <x-form.input name="display_order" type="number" label="ترتيب العرض" :value="old('display_order', $doctor?->display_order ?? 0)" min="0" />
    <label class="inline-flex min-h-11 items-center gap-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded border-border text-primary" @checked(old('is_active', $doctor?->is_active ?? true))>
        <span class="text-sm">نشط للحجز العام</span>
    </label>
</div>
