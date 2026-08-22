{{--
    Booking: selectable list card (clinic, doctor, service).
--}}
@props([
    'title',
    'description' => null,
])

<button
    type="button"
    {{ $attributes->class(['bk-select-card']) }}
>
    <div class="bk-select-card-body">
        <p class="bk-select-card-title">{{ $title }}</p>
        @if ($description)
            <p class="bk-select-card-desc">{{ $description }}</p>
        @endif
    </div>

    <div class="bk-select-card-icon">
        @isset($icon)
            {{ $icon }}
        @endisset
    </div>

    <span class="bk-select-card-check" aria-hidden="true">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
    </span>
</button>
