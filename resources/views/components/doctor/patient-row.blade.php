{{-- Doctor: Patient row — list card for patients page --}}
@props([
    'name',
    'phone',
    'lastVisit' => null,
    'visits' => 0,
    'notes' => null,
])

<div {{ $attributes->merge(['class' => 'ds-surface p-4 sm:p-5']) }}>
    <div class="flex items-start gap-3 sm:gap-4">
        <x-ui.avatar :name="$name" size="md" />
        <div class="min-w-0 flex-1 space-y-1.5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="truncate text-base font-bold text-foreground">{{ $name }}</p>
                <x-ui.badge variant="neutral" size="sm">{{ $visits }} زيارة</x-ui.badge>
            </div>
            <p class="text-sm text-foreground-muted" dir="ltr">{{ $phone }}</p>
            @if ($lastVisit)
                <p class="text-xs text-foreground-subtle">آخر زيارة: {{ $lastVisit }}</p>
            @endif
            @if ($notes)
                <p class="text-sm text-foreground-muted">{{ $notes }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>
