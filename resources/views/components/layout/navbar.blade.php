{{-- Layout: Navbar — sticky, safe-area aware --}}
@props([
    'brand' => config('clinic.name', config('app.name')),
    'showSidebarToggle' => true,
])

<header {{ $attributes->merge(['class' => 'sticky top-0 z-30 border-b border-navbar-border bg-navbar/95 backdrop-blur-md ds-safe-top']) }}>
    <div class="flex min-h-14 items-center justify-between gap-3 px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-2">
            @if ($showSidebarToggle)
                <button
                    type="button"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-navbar-foreground transition hover:bg-surface-subtle active:bg-surface-subtle lg:hidden"
                    @click="sidebarOpen = ! sidebarOpen"
                    aria-label="فتح القائمة"
                    aria-controls="doctor-sidebar"
                    :aria-expanded="sidebarOpen.toString()"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            @endif

            <span class="truncate text-sm font-bold text-navbar-foreground lg:hidden">{{ $brand }}</span>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>
</header>
