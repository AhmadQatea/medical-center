{{--
    Booking: Guest Fields
    Name + Syrian WhatsApp — accepts 09xxxxxxxx / +9639xxxxxxxx
--}}
@props([
    'nameModel' => 'patientName',
    'phoneModel' => 'patientPhone',
    'nameLabel' => 'الاسم الكامل',
    'namePlaceholder' => 'اسمك الكامل',
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="w-full">
        <label class="ds-label" for="name">
            <span>{{ $nameLabel }}</span>
            <span class="text-danger" title="مطلوب">*</span>
        </label>
        <input
            type="text"
            name="name"
            id="name"
            required
            autocomplete="name"
            placeholder="{{ $namePlaceholder }}"
            x-model="{{ $nameModel }}"
            class="ds-control !min-h-16 !rounded-2xl !text-lg"
        />
        <x-form.validation-error field="name" />
    </div>

    <div class="w-full">
        <label class="ds-label" for="phone">
            <span>رقم واتساب</span>
            <span class="text-danger" title="مطلوب">*</span>
        </label>
        <input
            type="tel"
            name="phone"
            id="phone"
            required
            autocomplete="tel"
            inputmode="tel"
            dir="ltr"
            placeholder="0959422413"
            x-model="{{ $phoneModel }}"
            x-on:blur="formatPhone()"
            class="ds-control !min-h-16 !rounded-2xl !text-lg"
        />
        <p class="mt-1.5 text-xs leading-relaxed text-foreground-muted">
            مثال: <span dir="ltr">0959422413</span> أو <span dir="ltr">+963959422413</span>
        </p>
        <p class="mt-1 text-sm font-medium text-danger" x-show="{{ $phoneModel }}.trim() && ! phoneValid" x-cloak>
            أدخل رقم موبايل سوري صحيح يبدأ بـ 09
        </p>
        <x-form.validation-error field="phone" />
    </div>
</div>
