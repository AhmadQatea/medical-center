<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="clinic">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'حجز موعد') — {{ $medicalCenterName ?? config('clinic.medical_center.name') }}</title>

    <x-theme.provider />

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=cairo:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/booking.css', 'resources/js/booking.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-surface focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-foreground focus:shadow-soft">
        تخطي إلى المحتوى
    </a>
    <div class="flex min-h-screen flex-col">
        <main id="main-content" class="flex-1" tabindex="-1">
            <x-layout.container width="booking" class="!py-4 sm:!py-6">
                <x-layout.flash-messages />
                @yield('content')
            </x-layout.container>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
