{{-- Booking: empty availability state --}}
@props([
    'title' => 'لا توجد مواعيد متاحة',
    'description' => 'لا توجد مواعيد متاحة حالياً، يرجى المحاولة لاحقاً.',
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-border bg-surface px-6 py-12 text-center shadow-soft']) }}>
    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-primary-soft text-primary" aria-hidden="true">
        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>

    <h2 class="text-xl font-bold text-foreground sm:text-2xl">{{ $title }}</h2>
    <p class="mx-auto mt-3 max-w-sm text-base leading-relaxed text-foreground-muted">
        {{ $description }}
    </p>

    @if (trim((string) $slot) !== '')
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
