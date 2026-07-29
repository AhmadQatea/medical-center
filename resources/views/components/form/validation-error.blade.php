{{-- Form: Validation Error --}}
@props([
    'field' => null,
    'message' => null,
])

@php
    $error = $message;

    if ($error === null && $field && isset($errors) && $errors->has($field)) {
        $error = $errors->first($field);
    }
@endphp

@if ($error)
    <p {{ $attributes->merge(['class' => 'mt-1.5 text-sm font-medium text-danger', 'role' => 'alert']) }}>
        {{ $error }}
    </p>
@elseif (trim((string) $slot) !== '')
    <p {{ $attributes->merge(['class' => 'mt-1.5 text-sm font-medium text-danger', 'role' => 'alert']) }}>
        {{ $slot }}
    </p>
@endif
