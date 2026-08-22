@extends('layouts.booking')

@section('title', 'اختر الطبيب')

@section('content')
    @php
        $doctorUrls = $doctors->mapWithKeys(fn ($doctor) => [(string) $doctor->id => route('booking.book', [$clinic, $doctor])])->all();
    @endphp

    <div
        class="bk-shell"
        x-data="bookingSelection({
            urls: {{ Js::from($doctorUrls) }},
            backUrl: {{ Js::from(route('booking.index')) }},
        })"
    >
        <header class="bk-header">
            <div class="bk-header-inner">
                <x-theme.logo :alt="config('clinic.brand.name')" letter="C" class="h-9 w-9 rounded-xl text-sm" />
                <span class="bk-brand-title">{{ config('clinic.brand.name') }}</span>
            </div>
            <x-booking.progress-bar :current="2" :total="6" class="mt-3" />
            <div class="bk-page-title mt-4">
                <h1>اختر الطبيب</h1>
                <p>{{ $clinic->name }}</p>
            </div>
        </header>

        <div class="bk-content">
            <div class="space-y-3" role="listbox" aria-label="اختر الطبيب">
                @foreach ($doctors as $doctor)
                    <button
                        type="button"
                        role="option"
                        class="bk-select-card"
                        x-on:click="select({{ $doctor->id }})"
                        x-bind:class="isSelected({{ $doctor->id }}) ? 'is-selected' : ''"
                        x-bind:aria-selected="isSelected({{ $doctor->id }}).toString()"
                    >
                        <div class="bk-select-card-body">
                            <p class="bk-select-card-title">{{ $doctor->name }}</p>
                            @if ($doctor->specialty)
                                <p class="bk-select-card-desc">{{ $doctor->specialty }}</p>
                            @endif
                        </div>
                        <div class="bk-select-card-icon">
                            <x-ui.avatar :name="$doctor->name" size="sm" class="!h-10 !w-10 !bg-transparent !text-[var(--booking-primary)] !shadow-none" />
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
                <button type="button" class="bk-btn bk-btn-soft" x-on:click="goBack()">
                    السابق
                </button>
            </div>
        </footer>
    </div>
@endsection
