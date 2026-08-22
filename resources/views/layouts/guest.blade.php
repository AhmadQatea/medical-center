<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="clinic">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('clinic.medical_center.name') }} — {{ config('clinic.brand.name') }}</title>

    <x-theme.provider />

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=cairo:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    <a href="#login-form" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-surface focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-foreground focus:shadow-soft">
        تخطي إلى النموذج
    </a>
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10 ds-safe-top ds-safe-bottom">
        <a href="{{ route('booking.index') }}" class="mb-8 flex flex-col items-center gap-3">
            <x-theme.logo :alt="config('clinic.medical_center.name')" letter="م" class="h-14 w-14 rounded-2xl" />
            <span class="text-base font-bold text-foreground">{{ config('clinic.medical_center.name') }}</span>
        </a>

        <div id="login-form" class="w-full max-w-md rounded-ds-lg border border-border bg-surface p-6 shadow-soft-md sm:p-8" tabindex="-1">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
