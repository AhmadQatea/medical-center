<x-guest-layout>
    <div class="mb-6 space-y-1.5 text-center">
        <h1 class="text-xl font-bold text-foreground">تسجيل الدخول</h1>
        <p class="text-sm text-foreground-muted">لوحة {{ config('clinic.medical_center.name') }}</p>
        <div class="ds-gold-line mx-auto mt-3" aria-hidden="true"></div>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4" :dismissible="true">{{ session('status') }}</x-ui.alert>
    @endif

    <form
        method="POST"
        action="{{ route('login') }}"
        class="space-y-5"
        x-data="{ submitting: false, showPassword: false }"
        x-on:submit="submitting = true"
    >
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

        <div class="w-full">
            <label class="ds-label" for="password">
                <span>كلمة المرور</span>
                <span class="text-danger" title="مطلوب">*</span>
            </label>

            <div class="relative">
                <input
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    id="password"
                    required
                    autocomplete="current-password"
                    class="ds-control !min-h-14 pe-14"
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                />

                <button
                    type="button"
                    class="absolute end-1.5 top-1/2 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-xl text-foreground-muted transition hover:bg-surface-subtle hover:text-foreground"
                    x-on:click="showPassword = ! showPassword"
                    x-bind:aria-label="showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                    x-bind:aria-pressed="showPassword.toString()"
                >
                    <svg x-show="! showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>

            <x-form.validation-error field="password" id="password-error" />
        </div>

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
