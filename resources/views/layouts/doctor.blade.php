<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="clinic">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'لوحة التحكم') — {{ config('clinic.brand.name') }}</title>

    <x-theme.provider />

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=cairo:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/doctor.css', 'resources/js/doctor.js'])
    @stack('styles')
</head>
<body class="h-screen overflow-hidden bg-background font-sans text-foreground antialiased" x-data="{
    sidebarOpen: false,
    isDesktop: window.matchMedia('(min-width: 1024px)').matches,
    init() {
        window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
            this.isDesktop = event.matches;
            if (event.matches) {
                this.sidebarOpen = false;
            }
        });
    },
}">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-surface focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-foreground focus:shadow-soft">
        تخطي إلى المحتوى
    </a>
    <div class="flex h-screen overflow-hidden">
        <x-layout.sidebar :brand="config('clinic.brand.name')">
            <x-layout.nav-item :href="route('doctor.dashboard')" :active="request()->routeIs('doctor.dashboard')" icon="dashboard">
                لوحة التحكم
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.bookings.index')" :active="request()->routeIs(['doctor.bookings.index', 'doctor.bookings.show', 'doctor.bookings.edit'])" icon="bookings">
                الحجوزات
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.bookings.instant')" :active="request()->routeIs('doctor.bookings.instant')" icon="instant">
                إضافة حجز
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.patients.index')" :active="request()->routeIs('doctor.patients.*')" icon="patients">
                المرضى
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.schedule.index')" :active="request()->routeIs('doctor.schedule.*')" icon="schedule">
                جداول الأطباء
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.timeline.index')" :active="request()->routeIs('doctor.timeline.*')" icon="timeline">
                الخط الزمني
            </x-layout.nav-item>

            @if (auth()->user()?->isAdmin())
                <div class="my-3 border-t border-sidebar-border pt-3">
                    <p class="mb-2 px-3.5 text-xs font-semibold uppercase tracking-wide text-foreground-subtle">إدارة المركز</p>
                    <x-layout.nav-item :href="route('doctor.clinics.index')" :active="request()->routeIs('doctor.clinics.*')" icon="clinic">
                        العيادات
                    </x-layout.nav-item>
                    <x-layout.nav-item :href="route('doctor.doctors.index')" :active="request()->routeIs('doctor.doctors.*')" icon="profile">
                        الأطباء
                    </x-layout.nav-item>
                </div>
            @endif

            <div class="my-3 border-t border-sidebar-border pt-3">
                <x-layout.nav-item :href="route('doctor.settings.index')" :active="request()->routeIs('doctor.settings.*')" icon="settings">
                    إعدادات الحجز العام
                </x-layout.nav-item>
                <x-layout.nav-item :href="route('doctor.profile.index')" :active="request()->routeIs('doctor.profile.*')" icon="profile">
                    الملف الشخصي
                </x-layout.nav-item>
            </div>

            <x-slot:footer>
                <a href="{{ route('booking.index') }}" target="_blank" rel="noopener" class="ds-nav-link mb-1">
                    <svg class="ds-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    <span>واتساب / الحجز العام</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ds-nav-link w-full text-start">
                        <svg class="ds-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        <span>تسجيل الخروج</span>
                    </button>
                </form>
            </x-slot:footer>
        </x-layout.sidebar>

        <div class="flex min-h-0 min-w-0 w-full flex-1 flex-col overflow-hidden">
            <x-layout.navbar />

            <main id="main-content" class="ds-scrollbar min-h-0 flex-1 overflow-y-auto overscroll-contain" tabindex="-1">
                <x-layout.container>
                    <x-layout.flash-messages />
                    @yield('content')
                </x-layout.container>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
