@props([
    'type' => null,
])

<x-ui.card>
    <div class="space-y-5">
        <x-form.input
            name="name"
            label="اسم نوع الموعد"
            placeholder="مثال: معاينة"
            :value="old('name', $type?->name)"
            required
            class="!min-h-14"
        />
        <x-form.textarea
            name="description"
            label="الوصف"
            placeholder="وصف اختياري"
            :value="old('description', $type?->description)"
            :rows="2"
            class="!min-h-14"
        />
        <x-form.input
            name="color"
            type="color"
            label="اللون"
            :value="old('color', $type?->color ?? '#6B1E2A')"
            dir="ltr"
            class="!min-h-14 !w-full"
        />
        @if ($type)
            <label class="inline-flex cursor-pointer items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="h-5 w-5 rounded border-border text-primary focus:ring-primary"
                    @checked(old('is_active', $type->is_active))
                >
                <span class="text-sm font-medium text-foreground">نشط — يظهر في صفحة الحجز</span>
            </label>
        @endif
    </div>
</x-ui.card>
