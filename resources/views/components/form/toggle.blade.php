{{-- Form: Toggle --}}
@props([
    'name',
    'label' => null,
    'checked' => false,
    'value' => '1',
])

<label {{ $attributes->merge(['class' => 'inline-flex cursor-pointer items-center gap-3']) }}>
    <span class="relative inline-flex">
        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            class="peer sr-only"
            @checked(old($name, $checked))
        />
        <span class="h-6 w-11 rounded-full bg-surface-subtle transition peer-checked:bg-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary-muted"></span>
        <span class="pointer-events-none absolute start-0.5 top-0.5 h-5 w-5 rounded-full bg-surface shadow-soft transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
    </span>

    @if ($label || trim((string) $slot) !== '')
        <span class="text-sm font-medium text-foreground">{{ $label ?? $slot }}</span>
    @endif
</label>
