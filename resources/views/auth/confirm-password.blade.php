<x-guest-layout>
    <div class="mb-6 space-y-2">
        <h1 class="text-xl font-bold text-foreground">تأكيد كلمة المرور</h1>
        <p class="text-sm text-foreground-muted">
            هذه منطقة محمية. يرجى تأكيد كلمة المرور للمتابعة.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <x-form.input
            name="password"
            type="password"
            label="كلمة المرور"
            required
            autocomplete="current-password"
            autofocus
        />

        <x-ui.button type="submit" variant="primary" class="w-full">
            تأكيد
        </x-ui.button>
    </form>
</x-guest-layout>
