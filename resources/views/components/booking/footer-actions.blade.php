{{-- Booking footer actions — use next/back slots for Alpine handlers --}}
<div {{ $attributes->merge(['class' => 'bk-footer-actions']) }}>
    {{ $next }}
    @isset($back)
        {{ $back }}
    @endisset
</div>
