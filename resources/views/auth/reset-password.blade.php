<x-guest-layout>
    <div class="mb-6 space-y-1">
        <h1 class="text-xl font-bold text-foreground">تعيين كلمة مرور جديدة</h1>
        <p class="text-sm text-foreground-muted">أدخل كلمة المرور الجديدة لحساب الطبيب.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form.input
            name="email"
            type="email"
            label="البريد الإلكتروني"
            :value="old('email', $request->email)"
            required
            autofocus
            autocomplete="username"
            dir="ltr"
        />

        <x-form.input
            name="password"
            type="password"
            label="كلمة المرور الجديدة"
            required
            autocomplete="new-password"
        />

        <x-form.input
            name="password_confirmation"
            type="password"
            label="تأكيد كلمة المرور"
            required
            autocomplete="new-password"
        />

        <x-ui.button type="submit" variant="primary" class="w-full">
            حفظ كلمة المرور
        </x-ui.button>
    </form>
</x-guest-layout>
