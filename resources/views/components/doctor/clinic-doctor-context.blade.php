{{--
    Admin clinic → doctor context selector (GET form).
    blocking=true (default): schedule / instant — must pick clinic then doctor.
    blocking=false: bookings list — optional filters, show all by default.
--}}
@props([
    'context',
    'action',
    'preserve' => [],
    'blocking' => true,
])

@php
    $clinic = $context['clinic'] ?? null;
    $doctor = $context['doctor'] ?? null;
    $state = $context['state'];
    $blocking = (bool) $blocking;
@endphp

<form method="get" action="{{ $action }}" class="space-y-5">
    <x-ui.card>
        <div class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.select
                    name="clinic_id"
                    label="العيادة"
                    :value="old('clinic_id', $clinic?->id)"
                    onchange="this.form.querySelector('[name=doctor_id]')?.remove(); this.form.submit()"
                >
                    <option value="">{{ $blocking ? '— اختر العيادة —' : 'كل العيادات' }}</option>
                    @foreach ($context['clinics'] as $item)
                        <option value="{{ $item->id }}" @selected((int) old('clinic_id', $clinic?->id) === $item->id)>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </x-form.select>

                @if ($clinic !== null)
                    <x-form.select
                        name="doctor_id"
                        label="الطبيب"
                        :value="old('doctor_id', $doctor?->id)"
                        :disabled="$state === \App\Services\AdminBookingContextService::STATE_NO_DOCTORS || $context['doctors']->isEmpty()"
                        onchange="this.form.submit()"
                    >
                        @if (! $blocking || $context['doctors']->count() !== 1)
                            <option value="">{{ $blocking ? '— اختر الطبيب —' : 'كل الأطباء' }}</option>
                        @endif
                        @foreach ($context['doctors'] as $item)
                            <option value="{{ $item->id }}" @selected((int) old('doctor_id', $doctor?->id) === $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                @endif
            </div>

            @if ($blocking && $doctor !== null && ($context['auto_selected_doctor'] ?? false))
                <p class="text-sm text-foreground-muted">
                    الطبيب المختار: <span class="font-semibold text-foreground">{{ $doctor->name }}</span>
                    <span class="text-foreground-subtle">(تم الاختيار تلقائياً)</span>
                </p>
            @endif

            @foreach ($preserve as $name => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach
        </div>
    </x-ui.card>
</form>

@if ($blocking)
    @if ($state === \App\Services\AdminBookingContextService::STATE_NEEDS_CLINIC)
        <x-ui.empty-state
            title="يرجى اختيار العيادة أولاً"
            description="يرجى اختيار العيادة أولاً لإدارة المواعيد."
            icon="calendar"
        />
    @elseif ($state === \App\Services\AdminBookingContextService::STATE_NO_DOCTORS)
        <x-ui.empty-state
            title="لا يوجد أطباء مرتبطون بهذه العيادة"
            description="لا يوجد أطباء مرتبطون بهذه العيادة."
            icon="user"
        />
    @elseif ($state === \App\Services\AdminBookingContextService::STATE_NEEDS_DOCTOR)
        <x-ui.empty-state
            title="يرجى اختيار الطبيب"
            description="اختر الطبيب لعرض المواعيد والجدول الخاص به."
            icon="user"
        />
    @endif
@elseif ($state === \App\Services\AdminBookingContextService::STATE_NO_DOCTORS)
    <x-ui.alert variant="warning" class="mt-2">
        لا يوجد أطباء مرتبطون بهذه العيادة.
    </x-ui.alert>
@endif
