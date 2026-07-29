<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="clinic">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'لوحة العيادة') — {{ $clinicBrand ?? config('clinic.name') }}</title>

    <x-theme.provider />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/doctor.css', 'resources/js/doctor.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased" x-data="{
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
    <div class="min-h-screen lg:flex">
        <x-layout.sidebar :brand="$clinicBrand ?? config('clinic.name')" :subtitle="$clinicDoctorName ?? config('clinic.doctor.name')">
            <x-layout.nav-item :href="route('doctor.dashboard')" :active="request()->routeIs('doctor.dashboard')" icon="dashboard">
                لوحة التحكم
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.bookings.instant')" :active="request()->routeIs('doctor.bookings.instant')" icon="instant">
                حجز فوري
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.bookings.index')" :active="request()->routeIs('doctor.bookings.index')" icon="bookings">
                المواعيد
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.timeline.index')" :active="request()->routeIs('doctor.timeline.*')" icon="timeline">
                الخط الزمني
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.schedule.index')" :active="request()->routeIs('doctor.schedule.*')" icon="schedule">
                إدارة الجدول
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.patients.index')" :active="request()->routeIs('doctor.patients.*')" icon="patients">
                المرضى
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.appointment-types.index')" :active="request()->routeIs('doctor.appointment-types.*')" icon="types">
                أنواع المواعيد
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.settings.index')" :active="request()->routeIs('doctor.settings.*')" icon="settings">
                إعدادات العيادة
            </x-layout.nav-item>
            <x-layout.nav-item :href="route('doctor.profile.index')" :active="request()->routeIs('doctor.profile.*')" icon="profile">
                الملف الشخصي
            </x-layout.nav-item>

            <x-slot:footer>
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

        <div class="flex min-w-0 flex-1 flex-col bg-background">
            <x-layout.navbar :brand="$clinicBrand ?? config('clinic.name')">
                <x-slot:actions>
                    @hasSection('navbar-actions')
                        @yield('navbar-actions')
                    @else
                        <x-ui.button href="{{ route('doctor.bookings.instant') }}" variant="primary" size="sm">
                            حجز فوري
                        </x-ui.button>
                    @endif
                </x-slot:actions>
            </x-layout.navbar>

            <main id="main-content" class="flex-1" tabindex="-1">
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
