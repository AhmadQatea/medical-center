{{--
    UI: Alert — success / warning / danger / info with icons for medical clarity.
--}}
@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $variants = [
        'info' => 'border-primary-muted bg-primary-soft text-foreground',
        'success' => 'border-success/30 bg-success-soft text-success-foreground',
        'warning' => 'border-warning/30 bg-warning-soft text-warning-foreground',
        'danger' => 'border-danger/30 bg-danger-soft text-danger-foreground',
    ];

    $icons = [
        'info' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
        'success' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'warning' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        'danger' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
    ];

    $iconColor = match ($variant) {
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-danger',
        default => 'text-primary',
    };
@endphp

<div
    @if ($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-init="setTimeout(() => show = false, 8000)"
    @endif
    {{ $attributes->merge([
        'class' => 'flex gap-3 rounded-2xl border px-4 py-3.5 text-sm shadow-soft '.($variants[$variant] ?? $variants['info']),
        'role' => 'alert',
    ]) }}
>
    <span class="mt-0.5 shrink-0 {{ $iconColor }}" aria-hidden="true">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$variant] ?? $icons['info'] }}" />
        </svg>
    </span>

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-bold leading-snug">{{ $title }}</p>
        @endif
        <div @class(['mt-0.5 leading-relaxed' => true, 'opacity-90' => (bool) $title])>{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl opacity-70 transition hover:bg-black/5 hover:opacity-100"
            @click="show = false"
            aria-label="إغلاق الإشعار"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
