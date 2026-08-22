{{-- Layout: Sidebar — CarePoint medical center navigation --}}
@props([
    'brand' => config('clinic.brand.name'),
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
        'class' => 'fixed inset-y-0 start-0 z-50 flex h-screen w-64 shrink-0 flex-col overflow-hidden border-e border-sidebar-border bg-sidebar shadow-soft-lg transition-transform duration-200 max-lg:-translate-x-full max-lg:rtl:translate-x-full lg:relative lg:z-auto lg:h-screen lg:translate-x-0 lg:shadow-none',
    ]) }}
    :class="sidebarOpen ? 'max-lg:!translate-x-0' : ''"
    :aria-hidden="isDesktop ? 'false' : (!sidebarOpen).toString()"
    @keydown.escape.window="if (!isDesktop) sidebarOpen = false"
>
    <div class="flex shrink-0 items-center gap-3 border-b border-sidebar-border px-5 py-4">
        <div class="min-w-0 flex-1">
            <p class="ds-brand-mark truncate">{{ $brand }}</p>
            @if ($subtitle)
                <p class="truncate text-xs text-foreground-muted">{{ $subtitle }}</p>
            @else
                <p class="truncate text-xs text-foreground-muted">{{ config('clinic.medical_center.name') }}</p>
            @endif
        </div>
        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-foreground-muted transition hover:bg-sidebar-muted lg:hidden"
            @click="sidebarOpen = false"
            aria-label="إغلاق القائمة"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav
        class="ds-scrollbar min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 py-3"
        aria-label="قائمة النظام"
        x-bind:inert="!isDesktop && !sidebarOpen"
    >
        {{ $slot }}
    </nav>

    @isset($footer)
        <div
            class="shrink-0 border-t border-sidebar-border px-3 py-3"
            x-bind:inert="!isDesktop && !sidebarOpen"
        >
            {{ $footer }}
        </div>
    @endisset
</aside>
