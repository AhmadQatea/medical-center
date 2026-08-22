@props(['clinic' => null])

<div class="grid gap-4">
    <x-form.input name="name" label="اسم العيادة" :value="old('name', $clinic?->name)" required />
    <x-form.input name="slug" label="الرابط المختصر (slug)" :value="old('slug', $clinic?->slug)" dir="ltr" />
    <x-form.input name="specialty" label="التخصص" :value="old('specialty', $clinic?->specialty)" />
    <x-form.textarea name="description" label="الوصف" :value="old('description', $clinic?->description)" rows="3" />
    <x-form.input name="display_order" type="number" label="ترتيب العرض" :value="old('display_order', $clinic?->display_order ?? 0)" min="0" />
    <label class="inline-flex min-h-11 items-center gap-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded border-border text-primary" @checked(old('is_active', $clinic?->is_active ?? true))>
        <span class="text-sm">نشطة للحجز العام</span>
    </label>
</div>
