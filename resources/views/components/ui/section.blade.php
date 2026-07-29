{{-- UI: Section --}}
@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0 space-y-1">
            @if ($title)
                <h2 class="text-base font-bold text-foreground sm:text-lg">{{ $title }}</h2>
            @endif
            @if ($description)
                <p class="text-sm text-foreground-muted">{{ $description }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    {{ $slot }}
</section>
