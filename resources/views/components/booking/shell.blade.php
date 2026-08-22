{{--
    Booking shell — header, optional progress, content, footer slot.
--}}
@props([
    'step' => null,
    'totalSteps' => 6,
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'bk-shell']) }}>
    <header class="bk-header">
        <div class="bk-header-inner">
            <x-theme.logo :alt="config('clinic.brand.name')" letter="C" class="h-9 w-9 rounded-xl text-sm" />
            <span class="bk-brand-title">{{ config('clinic.brand.name') }}</span>
        </div>

        @if ($step !== null)
            <x-booking.progress-bar :current="$step" :total="$totalSteps" class="mt-3" />
        @endif

        @if ($title)
            <div class="bk-page-title mt-4">
                <h1>{{ $title }}</h1>
                @if ($subtitle)
                    <p>{{ $subtitle }}</p>
                @endif
            </div>
        @endif
    </header>

    <div class="bk-content">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <footer class="bk-footer">
            {{ $footer }}
        </footer>
    @endif
</div>
