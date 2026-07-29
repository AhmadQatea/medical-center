{{-- Layout: Sidebar — desktop always visible; mobile off-canvas --}}
@props([
    'brand' => config('clinic.name', config('app.name')),
    'subtitle' => null,
])

<div
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-40 bg-overlay lg:hidden"
    @click="sidebarOpen = false"
    style="display: none;"
    aria-hidden="true"
></div>

<aside
    id="doctor-sidebar"
    {{ $attributes->merge([
        'class' => 'fixed inset-y-0 start-0 z-50 flex w-64 shrink-0 flex-col border-e border-sidebar-border bg-sidebar shadow-soft-lg transition-transform duration-200 max-lg:-translate-x-full max-lg:rtl:translate-x-full lg:static lg:translate-x-0 lg:shadow-none',
    ]) }}
    :class="sidebarOpen ? 'max-lg:!translate-x-0' : ''"
    :aria-hidden="isDesktop ? 'false' : (!sidebarOpen).toString()"
    @keydown.escape.window="if (!isDesktop) sidebarOpen = false"
>
    <div class="flex items-center gap-3 border-b border-sidebar-border px-4 py-4 ds-safe-top">
        <x-theme.logo :alt="$brand" letter="ع" class="h-10 w-10 rounded-2xl" />
        <div class="min-w-0">
            <p class="truncate text-sm font-bold text-foreground">{{ $brand }}</p>
            @if ($subtitle)
                <p class="truncate text-xs text-foreground-muted">{{ $subtitle }}</p>
            @endif
        </div>
        <button
            type="button"
            class="ms-auto inline-flex h-10 w-10 items-center justify-center rounded-xl text-foreground-muted transition hover:bg-sidebar-muted lg:hidden"
            @click="sidebarOpen = false"
            aria-label="إغلاق القائمة"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav
        class="flex-1 space-y-0.5 overflow-y-auto overscroll-contain p-2.5"
        aria-label="قائمة العيادة"
        x-bind:inert="!isDesktop && !sidebarOpen"
    >
        {{ $slot }}
    </nav>

    @isset($footer)
        <div
            class="border-t border-sidebar-border p-2.5 ds-safe-bottom"
            x-bind:inert="!isDesktop && !sidebarOpen"
        >
            {{ $footer }}
        </div>
    @endisset
</aside>
