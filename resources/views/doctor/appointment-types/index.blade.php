@extends('layouts.doctor')

@section('title', 'أنواع المواعيد')

@section('content')
    <x-layout.page-header title="أنواع المواعيد" description="إدارة أنواع الزيارات المعروضة في الحجز">
        <x-slot:actions>
            <x-ui.button href="{{ route('doctor.appointment-types.create') }}" variant="primary" size="sm">
                نوع جديد
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="ds-stack">
        <x-ui.card>
            <form method="get" class="grid gap-4 sm:grid-cols-[1fr_auto]">
                <x-form.input name="search" label="بحث" placeholder="اسم نوع الموعد" :value="request('search')" />
                <div class="flex items-end">
                    <x-ui.button type="submit" variant="soft" size="sm" class="!min-h-14 w-full sm:w-auto">بحث</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        @if ($types->isEmpty())
            <x-ui.empty-state
                title="لا توجد أنواع مواعيد بعد"
                description="أنشئ أنواع المواعيد التي يختار منها المرضى عند الحجز."
                icon="tag"
            >
                <x-ui.button href="{{ route('doctor.appointment-types.create') }}" variant="soft" size="sm">
                    إنشاء نوع موعد
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <form method="post" action="{{ route('doctor.appointment-types.reorder') }}" class="ds-stack">
                @csrf
                @method('PUT')

                <x-ui.card class="!p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-border bg-surface-subtle text-foreground-muted">
                                <tr>
                                    <th class="px-4 py-3 text-start font-semibold">الترتيب</th>
                                    <th class="px-4 py-3 text-start font-semibold">النوع</th>
                                    <th class="px-4 py-3 text-start font-semibold">الحالة</th>
                                    <th class="px-4 py-3 text-start font-semibold">الحجوزات</th>
                                    <th class="px-4 py-3 text-start font-semibold">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach ($types as $type)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <input type="hidden" name="order[]" value="{{ $type->id }}">
                                            <div class="flex items-center gap-2">
                                                @if (! $loop->first)
                                                    <button type="button" class="rounded-lg border border-border px-2 py-1 text-xs" onclick="moveRow(this, -1)">↑</button>
                                                @endif
                                                @if (! $loop->last)
                                                    <button type="button" class="rounded-lg border border-border px-2 py-1 text-xs" onclick="moveRow(this, 1)">↓</button>
                                                @endif
                                                <span class="text-foreground-muted">{{ $type->display_order }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-col gap-1">
                                                <x-ui.color-badge :name="$type->name" :color="$type->color" />
                                                @if ($type->description)
                                                    <span class="text-xs text-foreground-muted">{{ $type->description }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <x-ui.badge :variant="$type->is_active ? 'success' : 'neutral'" size="sm">
                                                {{ $type->is_active ? 'نشط' : 'موقوف' }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-4 font-semibold">{{ $type->appointments_count }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <x-ui.button href="{{ route('doctor.appointment-types.edit', $type) }}" variant="soft" size="sm">تعديل</x-ui.button>
                                                <form method="post" action="{{ route('doctor.appointment-types.toggle', $type) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <x-ui.button type="submit" variant="ghost" size="sm">
                                                        {{ $type->is_active ? 'إيقاف' : 'تفعيل' }}
                                                    </x-ui.button>
                                                </form>
                                                <form method="post" action="{{ route('doctor.appointment-types.destroy', $type) }}" onsubmit="return confirm('حذف نوع الموعد؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.button type="submit" variant="ghost" size="sm">حذف</x-ui.button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <x-ui.button type="submit" variant="secondary" size="sm" class="self-start">حفظ الترتيب</x-ui.button>
            </form>
        @endif
    </div>

    @push('scripts')
        <script>
            function moveRow(button, direction) {
                const row = button.closest('tr');
                const sibling = direction < 0 ? row.previousElementSibling : row.nextElementSibling;
                if (! sibling) return;
                const tbody = row.parentNode;
                if (direction < 0) tbody.insertBefore(row, sibling);
                else tbody.insertBefore(sibling, row);
            }
        </script>
    @endpush
@endsection
