{{-- Layout: Page Header — minimal medical hierarchy --}}
@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0 space-y-1.5">
        @if ($title)
            <h1 class="text-xl font-bold tracking-tight text-foreground sm:text-2xl">{{ $title }}</h1>
        @endif

        {{ $slot }}

        @if ($description)
            <p class="max-w-2xl text-sm leading-relaxed text-foreground-muted">{{ $description }}</p>
        @endif

        <div class="ds-gold-line !mt-3" aria-hidden="true"></div>
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
