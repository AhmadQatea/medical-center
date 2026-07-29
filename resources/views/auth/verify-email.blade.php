<x-guest-layout>
    <div class="mb-6 space-y-2">
        <h1 class="text-xl font-bold text-foreground">تأكيد البريد الإلكتروني</h1>
        <p class="text-sm text-foreground-muted">
            شكراً لتسجيل الدخول. يرجى تأكيد بريدك عبر الرابط المرسل إليك قبل المتابعة.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-ui.alert variant="success" class="mb-4">
            تم إرسال رابط تأكيد جديد إلى بريدك الإلكتروني.
        </x-ui.alert>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit" variant="primary">
                إعادة إرسال رابط التأكيد
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost">
                تسجيل الخروج
            </x-ui.button>
        </form>
    </div>
</x-guest-layout>
