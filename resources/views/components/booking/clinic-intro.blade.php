{{--
    Booking: Clinic Intro
    Compact premium header — logo, photo, name, specialty.
--}}
@props([
    'clinic' => null,
    'doctor' => null,
    'specialty' => null,
    'description' => null,
    'initials' => null,
])

@php
    $clinic = $clinic ?? config('clinic.default_department.name');
    $doctor = $doctor ?? '—';
    $specialty = $specialty ?? config('clinic.default_department.specialty');
    $description = $description ?? config('clinic.medical_center.description');
    $initials = $initials ?? \App\Support\Name::initials((string) $doctor);
@endphp

<header {{ $attributes->merge(['class' => 'flex items-center gap-4']) }}>
    <x-ui.avatar
        :name="$doctor"
        :initials="$initials"
        size="xl"
        :ring="true"
        class="!h-20 !w-20 !text-xl sm:!h-24 sm:!w-24"
    />

    <div class="min-w-0 flex-1 text-start">
        <div class="mb-1.5 flex items-center gap-2">
            <x-theme.logo :alt="$clinic" letter="ع" class="h-8 w-8 rounded-xl" />
            <p class="truncate text-xs font-medium text-foreground-muted sm:text-sm">{{ $clinic }}</p>
        </div>

        <h1 class="truncate text-xl font-bold tracking-tight text-foreground sm:text-2xl">
            {{ $doctor }}
        </h1>

        <p class="mt-1">
            <x-ui.badge variant="accent">{{ $specialty }}</x-ui.badge>
        </p>

        @if ($description)
            <p class="mt-2 line-clamp-2 text-sm leading-snug text-foreground-muted">
                {{ $description }}
            </p>
        @endif
    </div>
</header>
