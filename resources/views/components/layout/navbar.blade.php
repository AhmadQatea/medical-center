{{-- Layout: Navbar — CarePoint top bar --}}
@props([
    'title' => config('clinic.medical_center.name', 'نظام الإدارة'),
    'showSidebarToggle' => true,
])

<header {{ $attributes->merge(['class' => 'z-30 shrink-0 border-b border-navbar-border bg-navbar']) }}>
    <div class="flex min-h-[4.25rem] items-center justify-between gap-4 px-4 sm:px-6">
        <div class="flex min-w-0 flex-1 items-center gap-3">
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

            <div class="min-w-0">
                <p class="truncate text-lg font-bold text-primary sm:text-xl">{{ $title }}</p>
                <p class="hidden truncate text-xs text-foreground-muted sm:block">{{ config('clinic.brand.name') }}</p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            {{ $actions ?? '' }}
            <x-layout.user-menu />
        </div>
    </div>
</header>
