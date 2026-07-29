{{-- Form: Select — a11y invalid state --}}
@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
])

@php
    $current = old($name, $value);
    $hasError = isset($errors) && $errors->has($name);
    $errorId = $name.'-error';
@endphp

<div class="w-full">
    @if ($label)
        <x-form.label :value="$label" :required="$required" for="{{ $name }}" />
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($required) required @endif
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
        {{ $attributes->merge(['class' => 'ds-control']) }}
    >
        @if ($placeholder)
            <option value="" @selected($current === null || $current === '')>{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if ($help)
        <p class="mt-1.5 text-xs leading-relaxed text-foreground-muted">{{ $help }}</p>
    @endif

    <x-form.validation-error :field="$name" :id="$errorId" />
</div>
