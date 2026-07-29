{{--
    UI: Button — large touch targets, optional loading state.
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary text-primary-foreground shadow-soft hover:bg-primary-hover focus-visible:ring-primary-muted',
        'accent' => 'bg-accent text-accent-foreground shadow-soft hover:brightness-95 focus-visible:ring-accent',
        'secondary' => 'border border-border bg-surface text-foreground shadow-soft hover:bg-surface-muted focus-visible:ring-primary-muted',
        'soft' => 'bg-primary-soft text-primary hover:bg-primary-muted/40 focus-visible:ring-primary-muted',
        'ghost' => 'bg-transparent text-foreground-muted hover:bg-surface-subtle hover:text-foreground focus-visible:ring-primary-muted',
        'danger' => 'bg-danger text-primary-foreground shadow-soft hover:brightness-95 focus-visible:ring-danger-soft',
        'whatsapp' => 'bg-[#25D366] text-white shadow-soft hover:brightness-95 focus-visible:ring-[#25D366]/40',
    ];

    $sizes = [
        'sm' => 'min-h-11 gap-1.5 px-4 text-sm rounded-xl',
        'md' => 'min-h-12 gap-2 px-5 text-base rounded-xl',
        'lg' => 'min-h-14 gap-2.5 px-6 text-base font-semibold rounded-2xl',
        'xl' => 'min-h-16 gap-3 px-8 text-lg font-semibold rounded-2xl',
    ];

    $isDisabled = $disabled || $loading;

    $classes = trim(
        'inline-flex items-center justify-center font-medium transition ds-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 '
        .($variants[$variant] ?? $variants['primary']).' '
        .($sizes[$size] ?? $sizes['md'])
    );
@endphp

@if ($href && ! $isDisabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($isDisabled)
        @if ($loading) aria-busy="true" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if ($loading)
            <span class="ds-spinner" aria-hidden="true"></span>
            <span>{{ $attributes->get('loading-text') ?? 'جاري التنفيذ…' }}</span>
        @else
            {{ $slot }}
        @endif
    </button>
@endif
