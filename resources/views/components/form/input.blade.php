{{-- Form: Input — a11y invalid state + large touch target --}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'help' => null,
])

@php
    $current = old($name, $value);
    $hasError = isset($errors) && $errors->has($name);
    $errorId = $name.'-error';
    $helpId = $name.'-help';
@endphp

<div class="w-full">
    @if ($label)
        <x-form.label :value="$label" :required="$required" for="{{ $name }}" />
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $current }}"
        @if ($required) required @endif
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @elseif ($help) aria-describedby="{{ $helpId }}" @endif
        {{ $attributes->merge(['class' => 'ds-control']) }}
    />

    @if ($help)
        <p id="{{ $helpId }}" class="mt-1.5 text-xs leading-relaxed text-foreground-muted">{{ $help }}</p>
    @endif

    <x-form.validation-error :field="$name" :id="$errorId" />
</div>
