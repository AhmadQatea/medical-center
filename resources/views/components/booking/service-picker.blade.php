{{-- Booking: appointment type cards (step: service) --}}
@props([
    'types' => [],
    'typeModel' => 'appointmentTypeId',
])

<div {{ $attributes->merge(['class' => 'space-y-3']) }} role="listbox" aria-label="اختر الخدمة">
    @foreach ($types as $type)
        @php
            $typeId = $type->id ?? $type['id'];
            $typeName = $type->name ?? $type['name'];
            $typeColor = $type->color ?? $type['color'] ?? '#00696F';
        @endphp
        <button
            type="button"
            role="option"
            x-on:click="{{ $typeModel }} = '{{ $typeId }}'"
            x-bind:aria-selected="({{ $typeModel }} == '{{ $typeId }}').toString()"
            x-bind:class="{{ $typeModel }} == '{{ $typeId }}' ? 'is-selected' : ''"
            class="bk-select-card"
        >
            <div class="bk-select-card-body">
                <p class="bk-select-card-title">{{ $typeName }}</p>
                <p class="bk-select-card-desc">موعد {{ $typeName }}</p>
            </div>

            <div class="bk-select-card-icon" style="background-color: {{ $typeColor }}20; color: {{ $typeColor }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3-6.75h.008v.008H15.75V11.25Zm0 3h.008v.008H15.75V14.25Zm0 3h.008v.008H15.75V17.25Z" />
                </svg>
            </div>

            <span class="bk-select-card-check" aria-hidden="true">
                <svg class="h-3.5 w-3.5" x-show="{{ $typeModel }} == '{{ $typeId }}'" x-cloak fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </span>
        </button>
    @endforeach

    <input type="hidden" name="appointment_type_id" x-bind:value="{{ $typeModel }}">
    <x-form.validation-error field="appointment_type_id" />
</div>
