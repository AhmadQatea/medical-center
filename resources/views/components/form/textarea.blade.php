{{-- Form: Textarea --}}
@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 4,
    'required' => false,
    'help' => null,
])

@php
    $current = old($name, $value ?? (trim((string) $slot) !== '' ? (string) $slot : null));
@endphp

<div class="w-full">
    @if ($label)
        <x-form.label :value="$label" :required="$required" for="{{ $name }}" />
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'ds-control']) }}
    >{{ $current }}</textarea>

    @if ($help)
        <p class="mt-1.5 text-xs text-foreground-muted">{{ $help }}</p>
    @endif

    <x-form.validation-error :field="$name" />
</div>
