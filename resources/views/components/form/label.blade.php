{{-- Form: Label --}}
@props([
    'value' => null,
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'ds-label']) }}>
    <span>{{ $value ?? $slot }}</span>
    @if ($required)
        <span class="text-danger" title="مطلوب">*</span>
    @endif
</label>
