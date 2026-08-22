@extends('layouts.doctor')

@section('title', 'العيادات')

@section('content')
    <x-layout.page-header title="العيادات" description="إدارة أقسام المركز الطبي">
        <x-slot:actions>
            <x-ui.button href="{{ route('doctor.clinics.create') }}" variant="primary" size="sm">عيادة جديدة</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-border bg-surface-subtle text-foreground-muted">
                    <tr>
                        <th class="px-4 py-3 text-start font-semibold">الاسم</th>
                        <th class="px-4 py-3 text-start font-semibold">التخصص</th>
                        <th class="px-4 py-3 text-start font-semibold">الأطباء</th>
                        <th class="px-4 py-3 text-start font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-start font-semibold">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($clinics as $clinic)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $clinic->name }}</td>
                            <td class="px-4 py-3">{{ $clinic->specialty ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $clinic->doctors_count }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :variant="$clinic->is_active ? 'success' : 'neutral'">
                                    {{ $clinic->is_active ? 'نشطة' : 'متوقفة' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button href="{{ route('doctor.clinics.edit', $clinic) }}" variant="soft" size="sm">تعديل</x-ui.button>
                                    <form method="post" action="{{ route('doctor.clinics.toggle', $clinic) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="ghost" size="sm">{{ $clinic->is_active ? 'إيقاف' : 'تفعيل' }}</x-ui.button>
                                    </form>
                                    <form method="post" action="{{ route('doctor.clinics.destroy', $clinic) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه العيادة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" size="sm">حذف</x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-foreground-muted">لا توجد عيادات بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@endsection
