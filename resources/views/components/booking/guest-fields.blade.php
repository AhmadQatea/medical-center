{{--
    Booking: Guest Fields
    Name + Syrian WhatsApp (+963) — large touch inputs.
--}}
@props([
    'nameModel' => 'patientName',
    'phoneModel' => 'patientPhone',
    'nameLabel' => 'الاسم الكامل',
    'namePlaceholder' => 'اسمك الكامل',
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <x-form.input
        name="name"
        :label="$nameLabel"
        :placeholder="$namePlaceholder"
        required
        autocomplete="name"
        x-model="{{ $nameModel }}"
        class="!min-h-16 !rounded-2xl !text-lg"
    />

    <x-form.input
        name="phone"
        type="tel"
        label="رقم واتساب"
        help="يجب أن يبدأ بـ +963"
        placeholder="+9639xxxxxxxx"
        autocomplete="tel"
        dir="ltr"
        required
        x-model="{{ $phoneModel }}"
        class="!min-h-16 !rounded-2xl !text-lg"
    />
</div>
