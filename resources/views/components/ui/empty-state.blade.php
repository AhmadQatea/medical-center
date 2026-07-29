{{--
    UI: Empty State — calm medical placeholder.
--}}
@props([
    'title',
    'description' => null,
    'icon' => 'calendar',
])

@php
    $icons = [
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'clock' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'tag' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-border bg-surface-subtle/70 px-5 py-10 text-center sm:px-8']) }}>
    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-soft text-primary" aria-hidden="true">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] ?? $icons['calendar'] }}" />
        </svg>
    </div>
    <p class="text-base font-bold text-foreground sm:text-lg">{{ $title }}</p>
    @if ($description)
        <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-foreground-muted">{{ $description }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
