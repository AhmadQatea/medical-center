@extends('layouts.booking')

@section('title', 'اختر العيادة')

@section('content')
    @php
        $clinicUrls = $clinics->mapWithKeys(fn ($clinic) => [(string) $clinic->id => route('booking.clinic', $clinic)])->all();
    @endphp

    <div
        class="bk-shell"
        x-data="bookingSelection({ urls: {{ Js::from($clinicUrls) }} })"
    >
        <header class="bk-header">
            <div class="bk-header-inner">
                <x-theme.logo :alt="config('clinic.brand.name')" letter="C" class="h-9 w-9 rounded-xl text-sm" />
                <span class="bk-brand-title">{{ config('clinic.brand.name') }}</span>
            </div>
            <x-booking.progress-bar :current="1" :total="6" class="mt-3" />
            <div class="bk-page-title mt-4">
                <h1>اختر العيادة</h1>
                <p>{{ $medicalCenterName }}</p>
            </div>
        </header>

        <div class="bk-content">
            <div class="space-y-3" role="listbox" aria-label="اختر العيادة">
                @foreach ($clinics as $clinic)
                    <button
                        type="button"
                        role="option"
                        class="bk-select-card"
                        x-on:click="select({{ $clinic->id }})"
                        x-bind:class="isSelected({{ $clinic->id }}) ? 'is-selected' : ''"
                        x-bind:aria-selected="isSelected({{ $clinic->id }}).toString()"
                    >
                        <div class="bk-select-card-body">
                            <p class="bk-select-card-title">{{ $clinic->name }}</p>
                            @if ($clinic->specialty ?? $clinic->description)
                                <p class="bk-select-card-desc">{{ $clinic->specialty ?? $clinic->description }}</p>
                            @endif
                        </div>
                        <div class="bk-select-card-icon">
                            <x-ui.avatar :name="$clinic->name" size="sm" class="!h-10 !w-10 !bg-transparent !text-[var(--booking-primary)] !shadow-none" />
                        </div>
                        <span class="bk-select-card-check" aria-hidden="true">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <footer class="bk-footer">
            <div class="bk-footer-actions">
                <button
                    type="button"
                    class="bk-btn bk-btn-primary"
                    x-on:click="goNext()"
                    x-bind:disabled="! selected"
                >
                    التالي
                </button>
            </div>
        </footer>
    </div>
@endsection
