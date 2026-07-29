{{--
    Booking: dynamic appointment type select.
--}}
@props([
    'types' => [],
    'typeModel' => 'appointmentTypeId',
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="w-full">
        <label class="ds-label" for="appointment_type_id">
            <span>نوع الموعد</span>
            <span class="text-danger" title="مطلوب">*</span>
        </label>
        <select
            name="appointment_type_id"
            id="appointment_type_id"
            required
            x-model="{{ $typeModel }}"
            class="ds-control !min-h-16 !rounded-2xl !text-lg"
        >
            <option value="">اختر نوع الموعد</option>
            @foreach ($types as $type)
                <option value="{{ $type->id ?? $type['id'] }}">
                    {{ $type->name ?? $type['name'] }}
                </option>
            @endforeach
        </select>
        <x-form.validation-error field="appointment_type_id" />
    </div>
</div>
