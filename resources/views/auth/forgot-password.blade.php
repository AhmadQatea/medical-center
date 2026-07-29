<x-guest-layout>
    <div class="mb-6 space-y-2">
        <h1 class="text-xl font-bold text-foreground">استعادة كلمة المرور</h1>
        <p class="text-sm text-foreground-muted">
            أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
        </p>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-form.input
            name="email"
            type="email"
            label="البريد الإلكتروني"
            :value="old('email')"
            required
            autofocus
            dir="ltr"
        />

        <x-ui.button type="submit" variant="primary" class="w-full">
            إرسال رابط الاستعادة
        </x-ui.button>
    </form>
</x-guest-layout>
