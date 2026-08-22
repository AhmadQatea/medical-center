@extends('layouts.doctor')

@section('title', 'الأطباء')

@section('content')
    <x-layout.page-header title="الأطباء" description="إدارة أطباء المركز الطبي">
        <x-slot:actions>
            <x-ui.button href="{{ route('doctor.doctors.create') }}" variant="primary" size="sm">طبيب جديد</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-border bg-surface-subtle text-foreground-muted">
                    <tr>
                        <th class="px-4 py-3 text-start font-semibold">الاسم</th>
                        <th class="px-4 py-3 text-start font-semibold">الهاتف</th>
                        <th class="px-4 py-3 text-start font-semibold">العيادة</th>
                        <th class="px-4 py-3 text-start font-semibold">التخصص</th>
                        <th class="px-4 py-3 text-start font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-start font-semibold">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($doctors as $doctor)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $doctor->name }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ $doctor->phone ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $doctor->clinic?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $doctor->specialty ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :variant="$doctor->is_active ? 'success' : 'neutral'">
                                    {{ $doctor->is_active ? 'نشط' : 'متوقف' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button href="{{ route('doctor.doctors.edit', $doctor) }}" variant="soft" size="sm">تعديل</x-ui.button>
                                    <form method="post" action="{{ route('doctor.doctors.toggle', $doctor) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="ghost" size="sm">{{ $doctor->is_active ? 'إيقاف' : 'تفعيل' }}</x-ui.button>
                                    </form>
                                    @if ($canDeleteDoctor($doctor))
                                        <form method="post" action="{{ route('doctor.doctors.destroy', $doctor) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطبيب؟')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger" size="sm">حذف</x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-foreground-muted">لا يوجد أطباء بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $doctors->links() }}</div>
@endsection
