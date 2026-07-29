{{-- UI: Card — pure white surface, soft shadow --}}
@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'ds-surface overflow-hidden']) }}>
    @isset($header)
        <div class="border-b border-border px-5 py-4 sm:px-6">
            {{ $header }}
        </div>
    @endisset

    <div @class(['px-5 py-5 sm:px-6 sm:py-6' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-border bg-surface-muted px-5 py-4 sm:px-6">
            {{ $footer }}
        </div>
    @endisset
</div>
