<x-guest-layout>
    <div class="mb-6 space-y-1.5 text-center">
        <h1 class="text-xl font-bold text-foreground">تسجيل الدخول</h1>
        <p class="text-sm text-foreground-muted">لوحة {{ config('clinic.name') }}</p>
        <div class="ds-gold-line mx-auto mt-3" aria-hidden="true"></div>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4" :dismissible="true">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <x-form.input
            name="email"
            type="email"
            label="البريد الإلكتروني"
            :value="old('email')"
            required
            autofocus
            autocomplete="username"
            dir="ltr"
            class="!min-h-14"
        />

        <x-form.input
            name="password"
            type="password"
            label="كلمة المرور"
            required
            autocomplete="current-password"
            class="!min-h-14"
        />

        <label class="inline-flex min-h-11 items-center gap-3">
            <input type="checkbox" name="remember" class="h-5 w-5 rounded border-border text-primary focus:ring-primary-muted">
            <span class="text-sm text-foreground-muted">تذكرني</span>
        </label>

        <div class="flex flex-col gap-4">
            <x-ui.button type="submit" variant="primary" size="lg" class="w-full" x-bind:disabled="submitting">
                <span x-show="! submitting">دخول</span>
                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                    <span class="ds-spinner" aria-hidden="true"></span>
                    جاري الدخول…
                </span>
            </x-ui.button>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-center text-sm font-medium text-primary hover:underline">
                    نسيت كلمة المرور؟
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>
