@extends('layouts.doctor')

@section('title', 'الملف الشخصي')

@section('content')
    <x-layout.page-header
        title="الملف الشخصي"
        description="بيانات حساب {{ $user->name }}"
    />

    <div class="mx-auto max-w-xl ds-stack">
        <x-ui.card>
            <div class="mb-6 flex items-center gap-4">
                <x-ui.avatar
                    :name="$user->name"
                    :initials="\App\Support\Name::initials($user->name)"
                    size="xl"
                    :ring="true"
                />
                <div>
                    <p class="text-lg font-bold text-foreground">{{ $user->name }}</p>
                    <p class="text-sm text-foreground-muted">{{ $clinicSpecialty ?? config('clinic.doctor.specialty') }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('patch')
                <x-form.input name="name" label="الاسم" :value="old('name', $user->name)" required />
                <x-form.input name="email" type="email" label="البريد" :value="old('email', $user->email)" required dir="ltr" />
                <x-ui.button type="submit" variant="primary">حفظ</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-5 text-base font-bold text-foreground">كلمة المرور</h2>
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('put')
                <x-form.input name="current_password" type="password" label="الحالية" required autocomplete="current-password" />
                <x-form.input name="password" type="password" label="الجديدة" required autocomplete="new-password" />
                <x-form.input name="password_confirmation" type="password" label="تأكيد" required autocomplete="new-password" />
                <x-ui.button type="submit" variant="secondary">تحديث كلمة المرور</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card>
            <form method="POST" action="{{ route('logout') }}" class="flex items-center justify-between gap-4">
                @csrf
                <div>
                    <p class="font-bold text-foreground">تسجيل الخروج</p>
                    <p class="text-sm text-foreground-muted">إنهاء الجلسة الحالية</p>
                </div>
                <x-ui.button type="submit" variant="danger">خروج</x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
